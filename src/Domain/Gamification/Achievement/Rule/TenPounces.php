<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\PlayerStats;

final readonly class TenPounces extends Threshold
{
    public function id(): string
    {
        return 'pounce_10';
    }

    protected function measure(PlayerStats $stats): int
    {
        return $stats->doFirstCompleted;
    }

    protected function target(): int
    {
        return 10;
    }
}
