<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Greenhouse;

enum Facility: string
{
    case Glasshouse = 'glasshouse';
    case Condenser = 'condenser';
    case Misters = 'misters';
    case RainBarrel = 'rain_barrel';

    private const array GLASSHOUSE_COSTS = [2 => 80, 3 => 140, 4 => 260, 5 => 470, 6 => 840, 7 => 1500, 8 => 2700, 9 => 4900, 10 => 8800, 11 => 15800];
    private const array CONDENSER_COSTS = [2 => 60, 3 => 100, 4 => 170, 5 => 280, 6 => 460, 7 => 760, 8 => 1250, 9 => 2050, 10 => 3400];
    private const array MISTERS_COSTS = [1 => 120, 2 => 230, 3 => 430, 4 => 820, 5 => 1560, 6 => 2970, 7 => 5650, 8 => 10700, 9 => 20400, 10 => 38700];
    private const array RAIN_BARREL_COSTS = [1 => 200, 2 => 450, 3 => 1000, 4 => 2200];
    private const array CONDENSER_CAPACITIES = [1 => 100, 2 => 160, 3 => 250, 4 => 400, 5 => 650, 6 => 1000, 7 => 1600, 8 => 2600, 9 => 4000, 10 => 6500];
    private const int MISTERS_PERCENT_PER_LEVEL = 10;
    private const int BASE_WATERING_HOURS = 2;

    public function startLevel(): int
    {
        return match ($this) {
            self::Glasshouse, self::Condenser => 1,
            self::Misters, self::RainBarrel => 0,
        };
    }

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
            self::Condenser => self::CONDENSER_CAPACITIES[$level] ?? 0,
            self::Misters => self::MISTERS_PERCENT_PER_LEVEL * $level,
            self::RainBarrel => self::BASE_WATERING_HOURS + $level,
        };
    }

    /**
     * @return non-empty-array<int, int>
     */
    private function costs(): array
    {
        return match ($this) {
            self::Glasshouse => self::GLASSHOUSE_COSTS,
            self::Condenser => self::CONDENSER_COSTS,
            self::Misters => self::MISTERS_COSTS,
            self::RainBarrel => self::RAIN_BARREL_COSTS,
        };
    }
}
