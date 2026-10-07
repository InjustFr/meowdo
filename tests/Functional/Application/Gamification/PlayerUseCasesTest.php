<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Gamification;

use App\Application\Gamification\CollectSpecies;
use App\Application\Gamification\ListAchievements\AchievementView;
use App\Application\Gamification\ListAchievements\ListAchievementsHandler;
use App\Application\Gamification\ListHerbarium\ListHerbariumHandler;
use App\Application\Gamification\MarkAchievementsSeen\MarkAchievementsSeenHandler;
use App\Application\Gamification\PlayerView;
use App\Application\Gamification\ShowPlayer\ShowPlayerHandler;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
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

        self::assertSame(['Louis', 0, 1, 0, 100, 0, 0, 0, \count(SpeciesCatalog::all()), null, []], [$player->displayName, $player->xp, $player->level, $player->levelStartXp, $player->nextLevelXp, $player->streak, $player->bestStreak, $player->speciesCollected, $player->speciesTotal, $player->latestSpecimen, $player->newAchievements]);
    }

    public function testStreakFadesAfterAMissedDay(): void
    {
        self::completeTask(self::createTask('Vet'));

        self::freezeAt('2026-10-07 08:00 UTC');
        self::assertSame(1, $this->player()->streak);

        self::freezeAt('2026-10-08 08:00 UTC');
        self::assertSame([0, 1], [$this->player()->streak, $this->player()->bestStreak]);
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
        self::assertCount(12, $achievements);
        self::assertSame(['first_drop'], array_map(static fn (AchievementView $achievement): string => $achievement->id, $unlocked));
        self::assertEquals(new \DateTimeImmutable('2026-10-06 08:00 UTC'), new \DateTimeImmutable((string) $unlocked[0]->unlockedAt));
    }

    public function testTheHerbariumListsCollectedSpeciesOutOfTheCatalog(): void
    {
        self::getContainer()->get(CollectSpecies::class)($this->user, 2);
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        $herbarium = self::getContainer()->get(ListHerbariumHandler::class)();

        self::assertSame(\count(SpeciesCatalog::all()), $herbarium->total);
        self::assertCount(2, $herbarium->specimens);
        self::assertNotSame($herbarium->specimens[0]->species, $herbarium->specimens[1]->species);
        self::assertSame('2026-10-06T08:00:00+00:00', $herbarium->specimens[0]->collectedAt);
        self::assertSame([2, $herbarium->specimens[1]->species], [$this->player()->speciesCollected, $this->player()->latestSpecimen?->species]);
    }

    private function player(): PlayerView
    {
        return self::getContainer()->get(ShowPlayerHandler::class)();
    }
}
