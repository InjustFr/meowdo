<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

final class LevelCurve
{
    private const int STEP = 50;

    public static function thresholdOf(int $level): int
    {
        return self::STEP * $level * ($level - 1);
    }

    public static function levelFor(int $xp): int
    {
        $level = 1;
        while (self::thresholdOf($level + 1) <= $xp) {
            ++$level;
        }

        return $level;
    }
}
