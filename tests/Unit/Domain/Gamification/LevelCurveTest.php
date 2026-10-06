<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification;

use App\Domain\Gamification\LevelCurve;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LevelCurveTest extends TestCase
{
    public function testThresholds(): void
    {
        self::assertSame([0, 100, 300, 600, 1_000, 4_500], array_map(LevelCurve::thresholdOf(...), [1, 2, 3, 4, 5, 10]));
    }

    #[DataProvider('levels')]
    public function testLevelFor(int $xp, int $level): void
    {
        self::assertSame($level, LevelCurve::levelFor($xp));
    }

    /** @return iterable<array{int, int}> */
    public static function levels(): iterable
    {
        yield 'start' => [0, 1];
        yield 'just below level 2' => [99, 1];
        yield 'level 2' => [100, 2];
        yield 'just below level 3' => [299, 2];
        yield 'level 3' => [300, 3];
        yield 'level 4' => [600, 4];
        yield 'level 10' => [4_500, 10];
        yield 'negative' => [-10, 1];
    }
}
