<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification;

use App\Domain\Gamification\Reward;
use App\Domain\Gamification\RewardPolicy;
use App\Domain\Identity\User;
use App\Domain\Planning\Quadrant;
use App\Domain\Planning\Task;
use App\Domain\Shared\Day;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RewardPolicyTest extends TestCase
{
    private const string TODAY = '2026-10-06';

    #[DataProvider('rewards')]
    public function testRewardTable(?Quadrant $quadrant, ?string $dueOn, int $streak, int $xp): void
    {
        $now = new \DateTimeImmutable(self::TODAY.' 09:00');
        $task = Task::create(User::join('account', 'louis@example.com', 'Louis', 'Europe/Paris', $now), 'Vet', $now);
        if (null !== $quadrant) {
            $task->classify($quadrant, 0);
        }
        if (null !== $dueOn) {
            $task->dueBy(Day::of($dueOn));
        }

        self::assertEquals(new Reward($xp), new RewardPolicy()->rewardFor($task, Day::of(self::TODAY), $streak));
    }

    /** @return iterable<array{?Quadrant, ?string, int, int}> */
    public static function rewards(): iterable
    {
        yield 'plant pays most' => [Quadrant::Schedule, null, 0, 25];
        yield 'water now' => [Quadrant::DoFirst, null, 0, 20];
        yield 'trim' => [Quadrant::Delegate, null, 0, 10];
        yield 'compost' => [Quadrant::Eliminate, null, 0, 5];
        yield 'unsorted' => [null, null, 0, 8];
        yield 'on time on the deadline' => [Quadrant::DoFirst, self::TODAY, 0, 25];
        yield 'on time before the deadline' => [null, '2026-10-20', 0, 13];
        yield 'late gets no bonus' => [Quadrant::DoFirst, '2026-10-05', 0, 20];
        yield 'one streak day' => [Quadrant::DoFirst, null, 1, 21];
        yield 'three streak days' => [null, null, 3, 9];
        yield 'ten streak days' => [Quadrant::DoFirst, null, 10, 30];
        yield 'streak capped at ten days' => [Quadrant::DoFirst, null, 25, 30];
        yield 'everything' => [Quadrant::Schedule, self::TODAY, 10, 45];
        yield 'negative streak counts as none' => [Quadrant::Schedule, null, -3, 25];
    }
}
