<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification\Greenhouse;

use App\Domain\Gamification\Greenhouse\Facility;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FacilityTest extends TestCase
{
    private const array COSTS = [
        'glasshouse' => [2 => 80, 3 => 140, 4 => 260, 5 => 470, 6 => 840, 7 => 1500, 8 => 2700, 9 => 4900, 10 => 8800, 11 => 15800],
        'condenser' => [2 => 60, 3 => 100, 4 => 170, 5 => 280, 6 => 460, 7 => 760, 8 => 1250, 9 => 2050, 10 => 3400],
        'misters' => [1 => 120, 2 => 230, 3 => 430, 4 => 820, 5 => 1560, 6 => 2970, 7 => 5650, 8 => 10700, 9 => 20400, 10 => 38700],
        'rain_barrel' => [1 => 200, 2 => 450, 3 => 1000, 4 => 2200],
    ];

    #[DataProvider('costs')]
    public function testUpgradeCostTable(Facility $facility, int $level, int $cost): void
    {
        self::assertSame($cost, $facility->upgradeCost($level));
    }

    /** @return iterable<array{Facility, int, int}> */
    public static function costs(): iterable
    {
        foreach (self::COSTS as $facility => $costs) {
            foreach ($costs as $level => $cost) {
                yield $facility.' level '.$level => [Facility::from($facility), $level, $cost];
            }
        }
    }

    #[DataProvider('effects')]
    public function testEffectTable(Facility $facility, int $level, int $effect): void
    {
        self::assertSame($effect, $facility->effectAt($level));
    }

    /** @return iterable<array{Facility, int, int}> */
    public static function effects(): iterable
    {
        foreach (range(1, 11) as $level) {
            yield 'glasshouse level '.$level => [Facility::Glasshouse, $level, $level + 1];
        }
        foreach ([1 => 100, 2 => 160, 3 => 250, 4 => 400, 5 => 650, 6 => 1000, 7 => 1600, 8 => 2600, 9 => 4000, 10 => 6500] as $level => $capacity) {
            yield 'condenser level '.$level => [Facility::Condenser, $level, $capacity];
        }
        foreach (range(0, 10) as $level) {
            yield 'misters level '.$level => [Facility::Misters, $level, 10 * $level];
        }
        foreach (range(0, 4) as $level) {
            yield 'rain barrel level '.$level => [Facility::RainBarrel, $level, 2 + $level];
        }
    }

    #[DataProvider('levels')]
    public function testLevelsStartSmallAndStopAtTheirMaximum(Facility $facility, int $start, int $max): void
    {
        self::assertSame([$start, $max], [$facility->startLevel(), $facility->maxLevel()]);
        self::assertNull($facility->upgradeCost($start));
        self::assertNotNull($facility->upgradeCost($start + 1));
        self::assertNull($facility->upgradeCost($max + 1));
    }

    /** @return iterable<array{Facility, int, int}> */
    public static function levels(): iterable
    {
        yield 'glasshouse' => [Facility::Glasshouse, 1, 11];
        yield 'condenser' => [Facility::Condenser, 1, 10];
        yield 'misters' => [Facility::Misters, 0, 10];
        yield 'rain barrel' => [Facility::RainBarrel, 0, 4];
    }
}
