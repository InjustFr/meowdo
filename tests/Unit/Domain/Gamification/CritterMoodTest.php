<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification;

use App\Domain\Gamification\CritterMood;
use App\Domain\Gamification\Streak;
use App\Domain\Shared\Day;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CritterMoodTest extends TestCase
{
    public function testDormantBeforeTheFirstCompletion(): void
    {
        self::assertSame(CritterMood::Dormant, CritterMood::of(new Streak(), Day::of('2026-10-06')));
    }

    #[DataProvider('moods')]
    public function testMoodFollowsIdleDays(string $today, CritterMood $mood): void
    {
        $streak = new Streak()->record(Day::of('2026-10-06'));

        self::assertSame($mood, CritterMood::of($streak, Day::of($today)));
    }

    /** @return iterable<array{string, CritterMood}> */
    public static function moods(): iterable
    {
        yield 'done today' => ['2026-10-06', CritterMood::Lively];
        yield 'one idle day' => ['2026-10-07', CritterMood::Idle];
        yield 'two idle days' => ['2026-10-08', CritterMood::Idle];
        yield 'three idle days' => ['2026-10-09', CritterMood::Dormant];
        yield 'a month' => ['2026-11-06', CritterMood::Dormant];
    }
}
