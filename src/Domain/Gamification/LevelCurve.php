<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

final class LevelCurve
{
    public const int XP_PER_LEVEL = 150;

    public static function thresholdOf(int $level): int
    {
        return self::XP_PER_LEVEL * ($level - 1);
    }

    public static function levelFor(int $xp): int
    {
        return intdiv(max(0, $xp), self::XP_PER_LEVEL) + 1;
    }
}
