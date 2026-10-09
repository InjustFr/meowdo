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
        'misters' => [1 => 100, 2 => 180, 3 => 320, 4 => 560, 5 => 1000, 6 => 1800, 7 => 3200, 8 => 5600, 9 => 10000, 10 => 18000],
        'rain_barrel' => [1 => 150, 2 => 350, 3 => 800, 4 => 1800],
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
        self::assertSame($max, $facility->maxLevel());
        self::assertNull($facility->upgradeCost($start));
        self::assertNotNull($facility->upgradeCost($start + 1));
        self::assertNull($facility->upgradeCost($max + 1));
    }

    /** @return iterable<array{Facility, int, int}> */
    public static function levels(): iterable
    {
        yield 'glasshouse' => [Facility::Glasshouse, 1, 11];
        yield 'misters' => [Facility::Misters, 0, 10];
        yield 'rain barrel' => [Facility::RainBarrel, 0, 4];
    }
}
