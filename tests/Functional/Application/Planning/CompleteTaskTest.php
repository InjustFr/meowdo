<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Planning;

use App\Application\Gamification\ShowPlayer\ShowPlayerHandler;
use App\Application\Planning\ReopenTask\ReopenTaskHandler;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use App\Domain\Gamification\PlayerRepository;
use App\Domain\Gamification\Reward;
use App\Domain\Identity\User;
use App\Domain\Planning\Quadrant;
use App\Tests\Support\ActsAsUser;
use App\Tests\Support\FreezesClock;
use App\Tests\Support\PlansTasks;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;

final class CompleteTaskTest extends KernelTestCase
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

    public function testCompletingATaskEarnsItsReward(): void
    {
        $completion = self::completeTask(self::createTask('Plan the holidays', quadrant: Quadrant::Schedule));

        self::assertTrue($completion->task->done);
        self::assertEquals(new Reward(26), $completion->reward);
        self::assertNull($completion->leveledUpTo);
        self::assertSame([], $completion->newSpecies);
        self::assertSame([26, 1, 1, 0], [$completion->player->xp, $completion->player->level, $completion->player->streak, $completion->player->speciesCollected]);
    }

    public function testRewardIsEarnedOncePerTask(): void
    {
        $task = self::createTask('Vet', quadrant: Quadrant::DoFirst);
        self::completeTask($task);
        self::getContainer()->get(ReopenTaskHandler::class)(Ulid::fromString($task->id));

        $again = self::completeTask($task);

        self::assertNull($again->reward);
        self::assertSame(21, $again->player->xp);
    }

    public function testConsecutiveDaysBuildAStreakThatMultipliesXp(): void
    {
        foreach (['2026-10-04', '2026-10-05', '2026-10-06'] as $day) {
            self::freezeAt($day.' 08:00 UTC');
            $completion = self::completeTask(self::createTask('Water the fern '.$day, quadrant: Quadrant::DoFirst));
        }

        self::assertEquals(new Reward(23), $completion->reward);
        self::assertSame([3, 3], [$completion->player->streak, $completion->player->bestStreak]);
    }

    public function testLevellingUpIsReported(): void
    {
        self::getContainer()->get(PlayerRepository::class)->of($this->user)->earn(new Reward(140));
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        $completion = self::completeTask(self::createTask('Vet', quadrant: Quadrant::Schedule));

        self::assertSame(2, $completion->leveledUpTo);
        self::assertSame([2, 150, 300], [$completion->player->level, $completion->player->levelStartXp, $completion->player->nextLevelXp]);
    }

    public function testCrossingALevelCollectsOneNewSpecies(): void
    {
        self::getContainer()->get(PlayerRepository::class)->of($this->user)->earn(new Reward(140));
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        $completion = self::completeTask(self::createTask('Vet', quadrant: Quadrant::Schedule));

        self::assertCount(1, $completion->newSpecies);
        self::assertSame(1, $completion->player->speciesCollected);
        self::assertSame(SpeciesCatalog::get($completion->newSpecies[0]->slug)->rarity->value, $completion->newSpecies[0]->rarity);
    }

    public function testLevelsReachedWithoutCrossingThemHereCollectNothing(): void
    {
        self::getContainer()->get(PlayerRepository::class)->of($this->user)->earn(new Reward(400));
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        $completion = self::completeTask(self::createTask('Vet', quadrant: Quadrant::Schedule));

        self::assertNull($completion->leveledUpTo);
        self::assertSame([], $completion->newSpecies);
        self::assertSame(0, $completion->player->speciesCollected);
    }

    public function testFirstCompletionUnlocksFirstDrop(): void
    {
        $completion = self::completeTask(self::createTask('Vet'));

        self::assertSame(['first_drop'], $completion->player->newAchievements);
        self::assertSame(['first_drop'], self::getContainer()->get(ShowPlayerHandler::class)()->newAchievements);
    }
}
