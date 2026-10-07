<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Planning;

use App\Domain\Planning\Exception\InvalidRecurrence;
use App\Domain\Planning\Recurrence;
use App\Domain\Planning\RecurrenceUnit;
use App\Domain\Shared\Day;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RecurrenceTest extends TestCase
{
    #[DataProvider('steps')]
    public function testNextDay(int $interval, RecurrenceUnit $unit, string $day, string $expected): void
    {
        self::assertSame($expected, Day::format(new Recurrence($interval, $unit)->next(Day::of($day))));
    }

    /** @return iterable<array{int, RecurrenceUnit, string, string}> */
    public static function steps(): iterable
    {
        yield 'every day' => [1, RecurrenceUnit::Day, '2026-10-06', '2026-10-07'];
        yield 'every 3 days across months' => [3, RecurrenceUnit::Day, '2026-10-30', '2026-11-02'];
        yield 'every week' => [1, RecurrenceUnit::Week, '2026-10-06', '2026-10-13'];
        yield 'every 2 weeks across daylight saving time' => [2, RecurrenceUnit::Week, '2026-10-20', '2026-11-03'];
        yield 'every month' => [1, RecurrenceUnit::Month, '2026-10-06', '2026-11-06'];
        yield 'every month across years' => [1, RecurrenceUnit::Month, '2026-12-15', '2027-01-15'];
        yield 'every 13 months' => [13, RecurrenceUnit::Month, '2026-10-06', '2027-11-06'];
        yield 'month clamped to february' => [1, RecurrenceUnit::Month, '2027-01-31', '2027-02-28'];
        yield 'month clamped to a leap february' => [1, RecurrenceUnit::Month, '2028-01-31', '2028-02-29'];
        yield 'month clamped to a 30-day month' => [1, RecurrenceUnit::Month, '2026-10-31', '2026-11-30'];
        yield 'every year' => [1, RecurrenceUnit::Year, '2026-10-06', '2027-10-06'];
        yield 'leap day clamped the next year' => [1, RecurrenceUnit::Year, '2028-02-29', '2029-02-28'];
        yield 'leap day kept four years later' => [4, RecurrenceUnit::Year, '2028-02-29', '2032-02-29'];
    }

    public function testIgnoresTheTimeOfTheDay(): void
    {
        self::assertSame('2026-10-07', Day::format(new Recurrence(1, RecurrenceUnit::Day)->next(new \DateTimeImmutable('2026-10-06 23:30', new \DateTimeZone('Europe/Paris')))));
    }

    #[DataProvider('invalidIntervals')]
    public function testIntervalIsBetween1And365(int $interval): void
    {
        $this->expectExceptionObject(new InvalidRecurrence($interval));

        new Recurrence($interval, RecurrenceUnit::Week);
    }

    /** @return iterable<array{int}> */
    public static function invalidIntervals(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-2];
        yield 'over a year of days' => [366];
    }

    public function testBoundsAreAccepted(): void
    {
        self::assertSame(1, new Recurrence(1, RecurrenceUnit::Day)->interval);
        self::assertSame(365, new Recurrence(365, RecurrenceUnit::Day)->interval);
    }
}
