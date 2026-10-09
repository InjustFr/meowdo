<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification\Greenhouse;

use App\Domain\Gamification\Greenhouse\Facilities;
use App\Domain\Gamification\Greenhouse\Facility;
use PHPUnit\Framework\TestCase;

final class FacilitiesTest extends TestCase
{
    public function testFacilitiesStartAtTheirStartLevel(): void
    {
        $facilities = new Facilities();

        foreach (Facility::cases() as $facility) {
            self::assertSame($facility->startLevel(), $facilities->levelOf($facility));
        }
    }

    public function testRaisingOneFacilityLeavesTheOthersAlone(): void
    {
        $facilities = new Facilities();

        $raised = $facilities->raised(Facility::Misters)->raised(Facility::Misters)->raised(Facility::RainBarrel);

        self::assertSame([1, 2, 1], [$raised->glasshouse, $raised->misters, $raised->rainBarrel]);
        self::assertSame([1, 0, 0], [$facilities->glasshouse, $facilities->misters, $facilities->rainBarrel]);
        self::assertSame(20, $raised->effectOf(Facility::Misters));
    }
}
