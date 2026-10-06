<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement;

interface AchievementRule
{
    public function id(): string;

    public function isMetBy(PlayerStats $stats): bool;
}
