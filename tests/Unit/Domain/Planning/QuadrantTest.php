<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Planning;

use App\Domain\Planning\Quadrant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class QuadrantTest extends TestCase
{
    #[DataProvider('quadrants')]
    public function testOfUrgencyAndImportance(bool $urgent, bool $important, Quadrant $expected): void
    {
        $quadrant = Quadrant::of($urgent, $important);

        self::assertSame($expected, $quadrant);
        self::assertSame($urgent, $quadrant->isUrgent());
        self::assertSame($important, $quadrant->isImportant());
    }

    /** @return iterable<array{bool, bool, Quadrant}> */
    public static function quadrants(): iterable
    {
        yield 'water now' => [true, true, Quadrant::DoFirst];
        yield 'plant' => [false, true, Quadrant::Schedule];
        yield 'trim' => [true, false, Quadrant::Delegate];
        yield 'compost' => [false, false, Quadrant::Eliminate];
    }

    public function testPriorityOrdersWaterPlantTrimCompostThenUnsorted(): void
    {
        self::assertSame(
            [0, 1, 2, 3, 4],
            [Quadrant::DoFirst->priority(), Quadrant::Schedule->priority(), Quadrant::Delegate->priority(), Quadrant::Eliminate->priority(), Quadrant::UNSORTED_PRIORITY],
        );
    }
}
