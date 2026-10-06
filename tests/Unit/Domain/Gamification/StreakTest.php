<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification;

use App\Domain\Gamification\Streak;
use App\Domain\Shared\Day;
use PHPUnit\Framework\TestCase;

final class StreakTest extends TestCase
{
    public function testFirstActivityStartsAStreak(): void
    {
        $streak = new Streak()->record(new \DateTimeImmutable('2026-10-06 21:00'));

        self::assertSame(1, $streak->current);
        self::assertSame(1, $streak->best);
        self::assertEquals(Day::of('2026-10-06'), $streak->lastActiveOn);
    }

    public function testRecordingTheSameDayTwiceChangesNothing(): void
    {
        $streak = new Streak()->record(Day::of('2026-10-06'));

        self::assertSame($streak, $streak->record(Day::of('2026-10-06')));
        self::assertSame($streak, $streak->record(Day::of('2026-10-05')));
    }

    public function testConsecutiveDaysGrowTheStreak(): void
    {
        $streak = $this->activeOn('2026-10-04', '2026-10-05', '2026-10-06');

        self::assertSame(3, $streak->current);
        self::assertSame(3, $streak->best);
    }

    public function testAGapRestartsTheStreakButKeepsTheBest(): void
    {
        $streak = $this->activeOn('2026-10-01', '2026-10-02', '2026-10-03', '2026-10-05');

        self::assertSame(1, $streak->current);
        self::assertSame(3, $streak->best);

        $streak = $streak->record(Day::of('2026-10-06'));
        self::assertSame(2, $streak->current);
        self::assertSame(3, $streak->best);
    }

    public function testAsOfKeepsTheStreakUntilADayIsMissed(): void
    {
        $streak = $this->activeOn('2026-10-04', '2026-10-05');

        self::assertSame(2, $streak->asOf(Day::of('2026-10-05')));
        self::assertSame(2, $streak->asOf(Day::of('2026-10-06')));
        self::assertSame(0, $streak->asOf(Day::of('2026-10-07')));
        self::assertSame(0, new Streak()->asOf(Day::of('2026-10-07')));
    }

    public function testIdleDays(): void
    {
        $streak = $this->activeOn('2026-10-04');

        self::assertNull(new Streak()->idleDays(Day::of('2026-10-06')));
        self::assertSame(0, $streak->idleDays(Day::of('2026-10-04')));
        self::assertSame(2, $streak->idleDays(Day::of('2026-10-06')));
        self::assertSame(0, $streak->idleDays(Day::of('2026-10-01')));
    }

    private function activeOn(string ...$days): Streak
    {
        $streak = new Streak();
        foreach ($days as $day) {
            $streak = $streak->record(Day::of($day));
        }

        return $streak;
    }
}
