<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Planning;

use App\Application\Gamification\ShowPlayer\ShowPlayerHandler;
use App\Application\Planning\ReopenTask\ReopenTaskHandler;
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
        self::assertEquals(new Reward(26, 7), $completion->reward);
        self::assertNull($completion->leveledUpTo);
        self::assertSame([26, 7, 1, 1, 'purring'], [$completion->player->xp, $completion->player->coins, $completion->player->level, $completion->player->streak, $completion->player->cat->mood]);
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
            $completion = self::completeTask(self::createTask('Feed the cat '.$day, quadrant: Quadrant::DoFirst));
        }

        self::assertEquals(new Reward(23, 6), $completion->reward);
        self::assertSame([3, 3], [$completion->player->streak, $completion->player->bestStreak]);
    }

    public function testLevellingUpIsReported(): void
    {
        self::getContainer()->get(PlayerRepository::class)->of($this->user)->earn(new Reward(90, 0));
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        $completion = self::completeTask(self::createTask('Vet', quadrant: Quadrant::Schedule));

        self::assertSame(2, $completion->leveledUpTo);
        self::assertSame([2, 100, 300], [$completion->player->level, $completion->player->levelStartXp, $completion->player->nextLevelXp]);
    }

    public function testFirstCompletionUnlocksFirstPaw(): void
    {
        $completion = self::completeTask(self::createTask('Vet'));

        self::assertSame(['first_paw'], $completion->player->newAchievements);
        self::assertSame(['first_paw'], self::getContainer()->get(ShowPlayerHandler::class)()->newAchievements);
    }
}
