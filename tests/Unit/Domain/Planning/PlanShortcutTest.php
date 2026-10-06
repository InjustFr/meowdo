<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Planning;

use App\Domain\Planning\Exception\MissingPlanDate;
use App\Domain\Planning\PlanShortcut;
use App\Domain\Shared\Day;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlanShortcutTest extends TestCase
{
    #[DataProvider('shortcuts')]
    public function testResolvesFromToday(PlanShortcut $shortcut, string $today, ?string $expected): void
    {
        self::assertSame($expected, Day::format($shortcut->resolve(Day::of($today))));
    }

    /** @return iterable<array{PlanShortcut, string, ?string}> */
    public static function shortcuts(): iterable
    {
        yield 'today' => [PlanShortcut::Today, '2026-10-06', '2026-10-06'];
        yield 'tomorrow' => [PlanShortcut::Tomorrow, '2026-10-06', '2026-10-07'];
        yield 'tomorrow across months' => [PlanShortcut::Tomorrow, '2026-10-31', '2026-11-01'];
        yield 'tomorrow across years' => [PlanShortcut::Tomorrow, '2026-12-31', '2027-01-01'];
        yield 'next week from monday' => [PlanShortcut::NextWeek, '2026-10-05', '2026-10-12'];
        yield 'next week from tuesday' => [PlanShortcut::NextWeek, '2026-10-06', '2026-10-12'];
        yield 'next week from saturday' => [PlanShortcut::NextWeek, '2026-10-10', '2026-10-12'];
        yield 'next week from sunday' => [PlanShortcut::NextWeek, '2026-10-11', '2026-10-12'];
        yield 'not planned' => [PlanShortcut::None, '2026-10-06', null];
    }

    public function testIgnoresTheTimeOfToday(): void
    {
        self::assertSame('2026-10-07', Day::format(PlanShortcut::Tomorrow->resolve(new \DateTimeImmutable('2026-10-06 23:30'))));
    }

    public function testPickedDate(): void
    {
        self::assertSame('2026-12-24', Day::format(PlanShortcut::Date->resolve(Day::of('2026-10-06'), new \DateTimeImmutable('2026-12-24 18:00'))));
    }

    public function testPickedDateIsRequired(): void
    {
        $this->expectExceptionObject(new MissingPlanDate());

        PlanShortcut::Date->resolve(Day::of('2026-10-06'));
    }
}
