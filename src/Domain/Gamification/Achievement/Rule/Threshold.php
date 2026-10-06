<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\AchievementRule;
use App\Domain\Gamification\Achievement\PlayerStats;

abstract readonly class Threshold implements AchievementRule
{
    public function isMetBy(PlayerStats $stats): bool
    {
        return $this->measure($stats) >= $this->target();
    }

    abstract protected function measure(PlayerStats $stats): int;

    abstract protected function target(): int;
}
