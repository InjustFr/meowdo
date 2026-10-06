<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification;

use App\Domain\Gamification\CatMood;
use App\Domain\Gamification\Streak;
use App\Domain\Shared\Day;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CatMoodTest extends TestCase
{
    public function testSleepyBeforeTheFirstCompletion(): void
    {
        self::assertSame(CatMood::Sleepy, CatMood::of(new Streak(), Day::of('2026-10-06')));
    }

    #[DataProvider('moods')]
    public function testMoodFollowsIdleDays(string $today, CatMood $mood): void
    {
        $streak = new Streak()->record(Day::of('2026-10-06'));

        self::assertSame($mood, CatMood::of($streak, Day::of($today)));
    }

    /** @return iterable<array{string, CatMood}> */
    public static function moods(): iterable
    {
        yield 'done today' => ['2026-10-06', CatMood::Purring];
        yield 'one idle day' => ['2026-10-07', CatMood::Idle];
        yield 'two idle days' => ['2026-10-08', CatMood::Idle];
        yield 'three idle days' => ['2026-10-09', CatMood::Sleepy];
        yield 'a month' => ['2026-11-06', CatMood::Sleepy];
    }
}
