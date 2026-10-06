<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shared;

use App\Domain\Shared\Day;
use App\Domain\Shared\Exception\InvalidDay;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DayTest extends TestCase
{
    public function testOfParsesAMidnightDay(): void
    {
        self::assertSame('2026-10-06 00:00:00', Day::of('2026-10-06')->format('Y-m-d H:i:s'));
    }

    #[DataProvider('invalidDays')]
    public function testRejectsInvalidDays(string $day): void
    {
        $this->expectExceptionObject(new InvalidDay($day));

        Day::of($day);
    }

    /** @return iterable<array{string}> */
    public static function invalidDays(): iterable
    {
        yield 'empty' => [''];
        yield 'text' => ['tomorrow'];
        yield 'february 30' => ['2026-02-30'];
        yield 'month 13' => ['2026-13-01'];
        yield 'no padding' => ['2026-1-5'];
        yield 'with time' => ['2026-10-06 10:00'];
    }

    #[DataProvider('instants')]
    public function testAtUsesTheTimeZone(string $instant, string $zone, string $expected): void
    {
        self::assertSame($expected, Day::format(Day::at(new \DateTimeImmutable($instant), new \DateTimeZone($zone))));
    }

    /** @return iterable<array{string, string, string}> */
    public static function instants(): iterable
    {
        yield '23:30 UTC is already tomorrow in Paris' => ['2026-10-06 23:30 UTC', 'Europe/Paris', '2026-10-07'];
        yield '23:30 UTC is still today in UTC' => ['2026-10-06 23:30 UTC', 'UTC', '2026-10-06'];
        yield '02:00 UTC is still yesterday in New York' => ['2026-10-07 02:00 UTC', 'America/New_York', '2026-10-06'];
        yield 'winter time in Paris' => ['2026-12-31 23:30 UTC', 'Europe/Paris', '2027-01-01'];
    }

    public function testNormalizeDropsTimeAndZone(): void
    {
        self::assertEquals(Day::of('2026-10-06'), Day::normalize(new \DateTimeImmutable('2026-10-06 23:59', new \DateTimeZone('Pacific/Auckland'))));
    }

    public function testFormat(): void
    {
        self::assertNull(Day::format(null));
        self::assertSame('2026-10-06', Day::format(Day::of('2026-10-06')));
    }

    public function testStartOfIsMidnightInTheZone(): void
    {
        self::assertEquals(new \DateTimeImmutable('2026-10-05 22:00 UTC'), Day::startOf(Day::of('2026-10-06'), new \DateTimeZone('Europe/Paris')));
    }

    public function testDaysBetweenIsSigned(): void
    {
        self::assertSame(0, Day::daysBetween(new \DateTimeImmutable('2026-10-06 01:00'), new \DateTimeImmutable('2026-10-06 23:00')));
        self::assertSame(1, Day::daysBetween(Day::of('2026-10-06'), Day::of('2026-10-07')));
        self::assertSame(-3, Day::daysBetween(Day::of('2026-10-06'), Day::of('2026-10-03')));
        self::assertSame(1, Day::daysBetween(Day::of('2026-10-24'), Day::of('2026-10-25')));
    }
}
