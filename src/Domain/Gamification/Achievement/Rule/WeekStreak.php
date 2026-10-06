<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\PlayerStats;

final readonly class WeekStreak extends Threshold
{
    public function id(): string
    {
        return 'streak_7';
    }

    protected function measure(PlayerStats $stats): int
    {
        return $stats->bestStreak;
    }

    protected function target(): int
    {
        return 7;
    }
}
