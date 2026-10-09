<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Greenhouse;

enum Facility: string
{
    case Glasshouse = 'glasshouse';
    case Misters = 'misters';
    case RainBarrel = 'rain_barrel';

    private const array GLASSHOUSE_COSTS = [2 => 80, 3 => 140, 4 => 260, 5 => 470, 6 => 840, 7 => 1500, 8 => 2700, 9 => 4900, 10 => 8800, 11 => 15800];
    private const array MISTERS_COSTS = [1 => 100, 2 => 180, 3 => 320, 4 => 560, 5 => 1000, 6 => 1800, 7 => 3200, 8 => 5600, 9 => 10000, 10 => 18000];
    private const array RAIN_BARREL_COSTS = [1 => 150, 2 => 350, 3 => 800, 4 => 1800];
    private const int MISTERS_PERCENT_PER_LEVEL = 10;
    private const int BASE_WATERING_MULTIPLIER = 2;

    public function maxLevel(): int
    {
        return (int) array_key_last($this->costs());
    }

    public function upgradeCost(int $level): ?int
    {
        return $this->costs()[$level] ?? null;
    }

    public function effectAt(int $level): int
    {
        return match ($this) {
            self::Glasshouse => $level + 1,
            self::Misters => self::MISTERS_PERCENT_PER_LEVEL * $level,
            self::RainBarrel => self::BASE_WATERING_MULTIPLIER + $level,
        };
    }

    /**
     * @return non-empty-array<int, int>
     */
    private function costs(): array
    {
        return match ($this) {
            self::Glasshouse => self::GLASSHOUSE_COSTS,
            self::Misters => self::MISTERS_COSTS,
            self::RainBarrel => self::RAIN_BARREL_COSTS,
        };
    }
}
