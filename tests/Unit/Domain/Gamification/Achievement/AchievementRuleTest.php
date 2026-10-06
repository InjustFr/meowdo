<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification\Achievement;

use App\Domain\Gamification\Achievement\AchievementRule;
use App\Domain\Gamification\Achievement\PlayerStats;
use App\Domain\Gamification\Achievement\Rule\FirstPaw;
use App\Domain\Gamification\Achievement\Rule\FirstPurchase;
use App\Domain\Gamification\Achievement\Rule\FiveHundredTasks;
use App\Domain\Gamification\Achievement\Rule\HundredTasks;
use App\Domain\Gamification\Achievement\Rule\LevelFive;
use App\Domain\Gamification\Achievement\Rule\LevelTen;
use App\Domain\Gamification\Achievement\Rule\MatrixSorter;
use App\Domain\Gamification\Achievement\Rule\MonthStreak;
use App\Domain\Gamification\Achievement\Rule\PatientHunter;
use App\Domain\Gamification\Achievement\Rule\TenPounces;
use App\Domain\Gamification\Achievement\Rule\TenTasks;
use App\Domain\Gamification\Achievement\Rule\ThreeDayStreak;
use App\Domain\Gamification\Achievement\Rule\WeekStreak;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AchievementRuleTest extends TestCase
{
    /**
     * @param \Closure(int): PlayerStats $stats
     */
    #[DataProvider('rules')]
    public function testUnlocksAtItsThreshold(AchievementRule $rule, string $id, \Closure $stats, int $threshold): void
    {
        self::assertSame($id, $rule->id());
        self::assertFalse($rule->isMetBy($stats($threshold - 1)));
        self::assertTrue($rule->isMetBy($stats($threshold)));
        self::assertTrue($rule->isMetBy($stats($threshold + 1)));
    }

    /** @return iterable<array{AchievementRule, string, \Closure(int): PlayerStats, int}> */
    public static function rules(): iterable
    {
        $completed = static fn (int $count): PlayerStats => new PlayerStats(tasksCompleted: $count);

        yield 'first paw' => [new FirstPaw(), 'first_paw', $completed, 1];
        yield 'ten tasks' => [new TenTasks(), 'ten_tasks', $completed, 10];
        yield 'hundred tasks' => [new HundredTasks(), 'hundred_tasks', $completed, 100];
        yield 'five hundred tasks' => [new FiveHundredTasks(), 'five_hundred_tasks', $completed, 500];
        yield 'ten pounces' => [new TenPounces(), 'pounce_10', static fn (int $count): PlayerStats => new PlayerStats(doFirstCompleted: $count), 10];
        yield 'patient hunter' => [new PatientHunter(), 'stalk_25', static fn (int $count): PlayerStats => new PlayerStats(scheduleCompleted: $count), 25];
        yield 'matrix sorter' => [new MatrixSorter(), 'matrix_sorter', static fn (int $count): PlayerStats => new PlayerStats(tasksClassified: $count), 20];
        yield 'three day streak' => [new ThreeDayStreak(), 'streak_3', static fn (int $days): PlayerStats => new PlayerStats(bestStreak: $days), 3];
        yield 'week streak' => [new WeekStreak(), 'streak_7', static fn (int $days): PlayerStats => new PlayerStats(bestStreak: $days), 7];
        yield 'month streak' => [new MonthStreak(), 'streak_30', static fn (int $days): PlayerStats => new PlayerStats(bestStreak: $days), 30];
        yield 'level five' => [new LevelFive(), 'level_5', static fn (int $level): PlayerStats => new PlayerStats(level: $level), 5];
        yield 'level ten' => [new LevelTen(), 'level_10', static fn (int $level): PlayerStats => new PlayerStats(level: $level), 10];
        yield 'first purchase' => [new FirstPurchase(), 'first_purchase', static fn (int $count): PlayerStats => new PlayerStats(purchases: $count), 1];
    }

    public function testCompletedTasksDoNotCountForOtherRules(): void
    {
        $stats = new PlayerStats(tasksCompleted: 1_000);

        self::assertFalse(new TenPounces()->isMetBy($stats));
        self::assertFalse(new WeekStreak()->isMetBy($stats));
        self::assertFalse(new FirstPurchase()->isMetBy($stats));
    }
}
