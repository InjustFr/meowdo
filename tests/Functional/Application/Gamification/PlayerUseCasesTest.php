<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Gamification;

use App\Application\Gamification\BuyCosmetic\BuyCosmeticHandler;
use App\Application\Gamification\ListAchievements\AchievementView;
use App\Application\Gamification\ListAchievements\ListAchievementsHandler;
use App\Application\Gamification\ListShop\ListShopHandler;
use App\Application\Gamification\MarkAchievementsSeen\MarkAchievementsSeenHandler;
use App\Application\Gamification\PlayerView;
use App\Application\Gamification\RecoatCat\RecoatCatHandler;
use App\Application\Gamification\RenameCat\RenameCatHandler;
use App\Application\Gamification\ShowPlayer\ShowPlayerHandler;
use App\Application\Gamification\TakeOffCosmetic\TakeOffCosmeticHandler;
use App\Application\Gamification\WearCosmetic\WearCosmeticHandler;
use App\Domain\Gamification\Coat;
use App\Domain\Gamification\Cosmetic\CosmeticCatalog;
use App\Domain\Gamification\Cosmetic\Slot;
use App\Domain\Gamification\Exception\CosmeticAlreadyOwned;
use App\Domain\Gamification\Exception\CosmeticNotOwned;
use App\Domain\Gamification\Exception\EmptyCatName;
use App\Domain\Gamification\Exception\LevelTooLow;
use App\Domain\Gamification\Exception\NotEnoughCoins;
use App\Domain\Gamification\Exception\UnknownCosmetic;
use App\Domain\Gamification\PlayerRepository;
use App\Domain\Gamification\Reward;
use App\Domain\Identity\User;
use App\Tests\Support\ActsAsUser;
use App\Tests\Support\FreezesClock;
use App\Tests\Support\PlansTasks;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PlayerUseCasesTest extends KernelTestCase
{
    use ActsAsUser;
    use FreezesClock;
    use PlansTasks;

    private User $user;

    protected function setUp(): void
    {
        self::freezeAt('2026-10-06 08:00 UTC');
        $this->user = self::actAsNewUser();
    }

    public function testANewPlayer(): void
    {
        $player = $this->player();

        self::assertSame(['Louis', 0, 0, 1, 0, 100, 0, 0, []], [$player->displayName, $player->xp, $player->coins, $player->level, $player->levelStartXp, $player->nextLevelXp, $player->streak, $player->bestStreak, $player->newAchievements]);
        self::assertSame(['Mochi', 'ginger', 'sleepy'], [$player->cat->name, $player->cat->coat, $player->cat->mood]);
    }

    public function testStreakAndMoodFadeWithIdleDays(): void
    {
        self::completeTask(self::createTask('Vet'));

        self::freezeAt('2026-10-07 08:00 UTC');
        self::assertSame([1, 'idle'], [$this->player()->streak, $this->player()->cat->mood]);

        self::freezeAt('2026-10-08 08:00 UTC');
        self::assertSame([0, 1, 'idle'], [$this->player()->streak, $this->player()->bestStreak, $this->player()->cat->mood]);

        self::freezeAt('2026-10-09 08:00 UTC');
        self::assertSame('sleepy', $this->player()->cat->mood);
    }

    public function testNewAchievementsAreCelebratedOnce(): void
    {
        self::completeTask(self::createTask('Vet'));
        self::assertSame(['first_paw'], $this->player()->newAchievements);

        self::getContainer()->get(MarkAchievementsSeenHandler::class)();

        self::assertSame([], $this->player()->newAchievements);
    }

    public function testListAchievements(): void
    {
        self::completeTask(self::createTask('Vet'));

        $achievements = self::getContainer()->get(ListAchievementsHandler::class)();

        $unlocked = array_values(array_filter($achievements, static fn (AchievementView $achievement): bool => null !== $achievement->unlockedAt));
        self::assertCount(13, $achievements);
        self::assertSame(['first_paw'], array_map(static fn (AchievementView $achievement): string => $achievement->id, $unlocked));
        self::assertEquals(new \DateTimeImmutable('2026-10-06 08:00 UTC'), new \DateTimeImmutable((string) $unlocked[0]->unlockedAt));
    }

    public function testBuyingPutsTheItemOnTheCat(): void
    {
        $this->giveCoins(100);

        self::getContainer()->get(BuyCosmeticHandler::class)('party-hat');

        $player = $this->player();
        self::assertSame(70, $player->coins);
        self::assertSame('party-hat', $player->cat->outfit['hat']);
        self::assertSame(['first_purchase'], $player->newAchievements);
        self::assertSame(['owned' => true, 'worn' => true], $this->shopItem('party-hat'));
        self::assertSame(['owned' => false, 'worn' => false], $this->shopItem('beanie'));
    }

    public function testCannotBuyTwice(): void
    {
        $this->giveCoins(100);
        self::getContainer()->get(BuyCosmeticHandler::class)('party-hat');

        $this->expectExceptionObject(new CosmeticAlreadyOwned('party-hat'));

        self::getContainer()->get(BuyCosmeticHandler::class)('party-hat');
    }

    public function testCannotBuyWithoutEnoughCoins(): void
    {
        $this->giveCoins(10);

        $this->expectExceptionObject(new NotEnoughCoins('party-hat', 30, 10));

        self::getContainer()->get(BuyCosmeticHandler::class)('party-hat');
    }

    public function testCannotBuyAboveTheLevel(): void
    {
        $this->giveCoins(500);

        $this->expectExceptionObject(new LevelTooLow('crown', 8));

        self::getContainer()->get(BuyCosmeticHandler::class)('crown');
    }

    public function testCannotBuyAnUnknownItem(): void
    {
        $this->expectExceptionObject(new UnknownCosmetic('jetpack'));

        self::getContainer()->get(BuyCosmeticHandler::class)('jetpack');
    }

    public function testWearAndTakeOffOwnedItems(): void
    {
        $this->giveCoins(100);
        self::getContainer()->get(BuyCosmeticHandler::class)('party-hat');
        self::getContainer()->get(BuyCosmeticHandler::class)('beanie');
        self::assertSame('beanie', $this->player()->cat->outfit['hat']);

        self::getContainer()->get(WearCosmeticHandler::class)('party-hat');
        self::assertSame(['owned' => true, 'worn' => true], $this->shopItem('party-hat'));
        self::assertSame(['owned' => true, 'worn' => false], $this->shopItem('beanie'));

        self::getContainer()->get(TakeOffCosmeticHandler::class)(Slot::Hat);
        self::assertSame(['hat' => null, 'neckwear' => null, 'toy' => null, 'backdrop' => null], $this->player()->cat->outfit);
    }

    public function testCannotWearAnItemNotOwned(): void
    {
        $this->expectExceptionObject(new CosmeticNotOwned('beanie'));

        self::getContainer()->get(WearCosmeticHandler::class)('beanie');
    }

    public function testRenameAndRecoatTheCat(): void
    {
        self::getContainer()->get(RenameCatHandler::class)(' Biscuit ');
        self::getContainer()->get(RecoatCatHandler::class)(Coat::Tuxedo);

        self::assertSame(['Biscuit', 'tuxedo'], [$this->player()->cat->name, $this->player()->cat->coat]);
    }

    public function testCatNameIsRequired(): void
    {
        $this->expectExceptionObject(new EmptyCatName());

        self::getContainer()->get(RenameCatHandler::class)('  ');
    }

    public function testShopListsTheWholeCatalog(): void
    {
        $shop = self::getContainer()->get(ListShopHandler::class)();

        self::assertCount(\count(CosmeticCatalog::all()), $shop);
        self::assertEquals(['slug' => 'frog-hat', 'slot' => 'hat', 'price' => 60, 'minLevel' => 3, 'owned' => false, 'worn' => false], (array) $shop[2]);
    }

    private function player(): PlayerView
    {
        return self::getContainer()->get(ShowPlayerHandler::class)();
    }

    private function giveCoins(int $coins): void
    {
        self::getContainer()->get(PlayerRepository::class)->of($this->user)->earn(new Reward(0, $coins));
        self::getContainer()->get(EntityManagerInterface::class)->flush();
    }

    /**
     * @return array{owned: bool, worn: bool}
     */
    private function shopItem(string $slug): array
    {
        foreach (self::getContainer()->get(ListShopHandler::class)() as $item) {
            if ($slug === $item->slug) {
                return ['owned' => $item->owned, 'worn' => $item->worn];
            }
        }

        self::fail(\sprintf('%s is not in the shop.', $slug));
    }
}
