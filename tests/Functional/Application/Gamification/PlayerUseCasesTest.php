<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Gamification;

use App\Application\Gamification\BuyCosmetic\BuyCosmeticHandler;
use App\Application\Gamification\ListAchievements\AchievementView;
use App\Application\Gamification\ListAchievements\ListAchievementsHandler;
use App\Application\Gamification\ListShop\ListShopHandler;
use App\Application\Gamification\MarkAchievementsSeen\MarkAchievementsSeenHandler;
use App\Application\Gamification\PlayerView;
use App\Application\Gamification\RenameCritter\RenameCritterHandler;
use App\Application\Gamification\RetintCritter\RetintCritterHandler;
use App\Application\Gamification\ShowPlayer\ShowPlayerHandler;
use App\Application\Gamification\TakeOffCosmetic\TakeOffCosmeticHandler;
use App\Application\Gamification\WearCosmetic\WearCosmeticHandler;
use App\Domain\Gamification\Cosmetic\CosmeticCatalog;
use App\Domain\Gamification\Cosmetic\Slot;
use App\Domain\Gamification\Exception\CosmeticAlreadyOwned;
use App\Domain\Gamification\Exception\CosmeticNotOwned;
use App\Domain\Gamification\Exception\EmptyCritterName;
use App\Domain\Gamification\Exception\LevelTooLow;
use App\Domain\Gamification\Exception\NotEnoughCoins;
use App\Domain\Gamification\Exception\UnknownCosmetic;
use App\Domain\Gamification\PlayerRepository;
use App\Domain\Gamification\Reward;
use App\Domain\Gamification\Tint;
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
        self::assertSame(['Pip', 'sprout', 'dormant'], [$player->critter->name, $player->critter->tint, $player->critter->mood]);
    }

    public function testStreakAndMoodFadeWithIdleDays(): void
    {
        self::completeTask(self::createTask('Vet'));

        self::freezeAt('2026-10-07 08:00 UTC');
        self::assertSame([1, 'idle'], [$this->player()->streak, $this->player()->critter->mood]);

        self::freezeAt('2026-10-08 08:00 UTC');
        self::assertSame([0, 1, 'idle'], [$this->player()->streak, $this->player()->bestStreak, $this->player()->critter->mood]);

        self::freezeAt('2026-10-09 08:00 UTC');
        self::assertSame('dormant', $this->player()->critter->mood);
    }

    public function testNewAchievementsAreCelebratedOnce(): void
    {
        self::completeTask(self::createTask('Vet'));
        self::assertSame(['first_drop'], $this->player()->newAchievements);

        self::getContainer()->get(MarkAchievementsSeenHandler::class)();

        self::assertSame([], $this->player()->newAchievements);
    }

    public function testListAchievements(): void
    {
        self::completeTask(self::createTask('Vet'));

        $achievements = self::getContainer()->get(ListAchievementsHandler::class)();

        $unlocked = array_values(array_filter($achievements, static fn (AchievementView $achievement): bool => null !== $achievement->unlockedAt));
        self::assertCount(13, $achievements);
        self::assertSame(['first_drop'], array_map(static fn (AchievementView $achievement): string => $achievement->id, $unlocked));
        self::assertEquals(new \DateTimeImmutable('2026-10-06 08:00 UTC'), new \DateTimeImmutable((string) $unlocked[0]->unlockedAt));
    }

    public function testBuyingPutsTheItemOnTheCritter(): void
    {
        $this->giveCoins(100);

        self::getContainer()->get(BuyCosmeticHandler::class)('acorn-cap');

        $player = $this->player();
        self::assertSame(70, $player->coins);
        self::assertSame('acorn-cap', $player->critter->outfit['hat']);
        self::assertSame(['first_purchase'], $player->newAchievements);
        self::assertSame(['owned' => true, 'worn' => true], $this->shopItem('acorn-cap'));
        self::assertSame(['owned' => false, 'worn' => false], $this->shopItem('beanie'));
    }

    public function testCannotBuyTwice(): void
    {
        $this->giveCoins(100);
        self::getContainer()->get(BuyCosmeticHandler::class)('acorn-cap');

        $this->expectExceptionObject(new CosmeticAlreadyOwned('acorn-cap'));

        self::getContainer()->get(BuyCosmeticHandler::class)('acorn-cap');
    }

    public function testCannotBuyWithoutEnoughCoins(): void
    {
        $this->giveCoins(10);

        $this->expectExceptionObject(new NotEnoughCoins('acorn-cap', 30, 10));

        self::getContainer()->get(BuyCosmeticHandler::class)('acorn-cap');
    }

    public function testCannotBuyAboveTheLevel(): void
    {
        $this->giveCoins(500);

        $this->expectExceptionObject(new LevelTooLow('flower-crown', 8));

        self::getContainer()->get(BuyCosmeticHandler::class)('flower-crown');
    }

    public function testCannotBuyAnUnknownItem(): void
    {
        $this->expectExceptionObject(new UnknownCosmetic('jetpack'));

        self::getContainer()->get(BuyCosmeticHandler::class)('jetpack');
    }

    public function testWearAndTakeOffOwnedItems(): void
    {
        $this->giveCoins(100);
        self::getContainer()->get(BuyCosmeticHandler::class)('acorn-cap');
        self::getContainer()->get(BuyCosmeticHandler::class)('beanie');
        self::assertSame('beanie', $this->player()->critter->outfit['hat']);

        self::getContainer()->get(WearCosmeticHandler::class)('acorn-cap');
        self::assertSame(['owned' => true, 'worn' => true], $this->shopItem('acorn-cap'));
        self::assertSame(['owned' => true, 'worn' => false], $this->shopItem('beanie'));

        self::getContainer()->get(TakeOffCosmeticHandler::class)(Slot::Hat);
        self::assertSame(['hat' => null, 'neckwear' => null, 'toy' => null, 'backdrop' => null], $this->player()->critter->outfit);
    }

    public function testCannotWearAnItemNotOwned(): void
    {
        $this->expectExceptionObject(new CosmeticNotOwned('beanie'));

        self::getContainer()->get(WearCosmeticHandler::class)('beanie');
    }

    public function testRenameAndRetintTheCritter(): void
    {
        self::getContainer()->get(RenameCritterHandler::class)(' Bramble ');
        self::getContainer()->get(RetintCritterHandler::class)(Tint::Peat);

        self::assertSame(['Bramble', 'peat'], [$this->player()->critter->name, $this->player()->critter->tint]);
    }

    public function testCritterNameIsRequired(): void
    {
        $this->expectExceptionObject(new EmptyCritterName());

        self::getContainer()->get(RenameCritterHandler::class)('  ');
    }

    public function testShopListsTheWholeCatalog(): void
    {
        $shop = self::getContainer()->get(ListShopHandler::class)();

        self::assertCount(\count(CosmeticCatalog::all()), $shop);
        self::assertEquals(['slug' => 'toadstool', 'slot' => 'hat', 'price' => 60, 'minLevel' => 3, 'owned' => false, 'worn' => false], (array) $shop[2]);
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
