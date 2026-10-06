<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification\Achievement;

use App\Domain\Gamification\Achievement\AchievementReferee;
use App\Domain\Gamification\Achievement\AchievementRule;
use App\Domain\Gamification\Achievement\PlayerStats;
use App\Domain\Gamification\Achievement\Rule\FirstPaw;
use App\Domain\Gamification\Achievement\Rule\FirstPurchase;
use App\Domain\Gamification\Achievement\Rule\TenTasks;
use App\Domain\Gamification\Achievement\Rule\ThreeDayStreak;
use PHPUnit\Framework\TestCase;

final class AchievementRefereeTest extends TestCase
{
    public function testReturnsOnlyRulesMetAndNotYetUnlocked(): void
    {
        $referee = new AchievementReferee(new \ArrayIterator([new FirstPaw(), new TenTasks(), new ThreeDayStreak(), new FirstPurchase()]));

        $newlyMet = $referee->newlyMet(new PlayerStats(tasksCompleted: 12, bestStreak: 1), ['first_paw']);

        self::assertSame(['ten_tasks'], array_map(static fn (AchievementRule $rule): string => $rule->id(), $newlyMet));
    }

    public function testNothingIsMetAtTheStart(): void
    {
        $referee = new AchievementReferee([new FirstPaw(), new TenTasks()]);

        self::assertSame([], $referee->newlyMet(new PlayerStats(), []));
        self::assertCount(2, $referee->rules());
    }
}
