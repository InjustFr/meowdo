<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

use App\Domain\Planning\Quadrant;
use App\Domain\Planning\Task;

final readonly class RewardPolicy
{
    public const int UNSORTED_XP = 8;
    public const int ON_TIME_BONUS = 5;
    public const int MAX_STREAK_BONUS_DAYS = 10;
    public const float STREAK_BONUS_PER_DAY = 0.05;

    public function rewardFor(Task $task, \DateTimeImmutable $today, int $streak): Reward
    {
        $base = $this->baseXp($task->quadrant()) + ($task->isOnTime($today) ? self::ON_TIME_BONUS : 0);
        $multiplier = 1 + self::STREAK_BONUS_PER_DAY * min(max($streak, 0), self::MAX_STREAK_BONUS_DAYS);

        return new Reward((int) round($base * $multiplier));
    }

    private function baseXp(?Quadrant $quadrant): int
    {
        return match ($quadrant) {
            Quadrant::Schedule => 25,
            Quadrant::DoFirst => 20,
            Quadrant::Delegate => 10,
            Quadrant::Eliminate => 5,
            null => self::UNSORTED_XP,
        };
    }
}
