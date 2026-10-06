<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\PlayerStats;

final readonly class LevelTen extends Threshold
{
    public function id(): string
    {
        return 'level_10';
    }

    protected function measure(PlayerStats $stats): int
    {
        return $stats->level;
    }

    protected function target(): int
    {
        return 10;
    }
}
