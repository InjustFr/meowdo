<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Planning;

use App\Application\Planning\ReopenTask\ReopenTaskHandler;
use App\Application\Planning\ShowStatistics\DayCountView;
use App\Application\Planning\ShowStatistics\ProjectCountView;
use App\Application\Planning\ShowStatistics\ShowStatisticsHandler;
use App\Application\Planning\ShowStatistics\StatisticsView;
use App\Application\Planning\ShowStatistics\TotalsView;
use App\Domain\Planning\ProjectColor;
use App\Domain\Planning\Quadrant;
use App\Tests\Support\ActsAsUser;
use App\Tests\Support\FreezesClock;
use App\Tests\Support\PlansTasks;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;

final class StatisticsTest extends KernelTestCase
{
    use ActsAsUser;
    use FreezesClock;
    use PlansTasks;

    public function testStatisticsCountCompletionsPerDayOfTheUsersTimezone(): void
    {
        self::freezeAt('2026-08-20 10:00 UTC');
        self::actAsNewUser('Europe/Paris');
        $garden = self::createProject('Garden', ProjectColor::Moss)->id;
        self::completeTask(self::createTask('August'));
        self::freezeAt('2026-09-30 10:00 UTC');
        self::completeTask(self::createTask('Inbox, unsorted'));
        self::freezeAt('2026-10-04 21:30 UTC');
        self::completeTask(self::createTask('Sunday night, on time', dueOn: '2026-10-04', projectId: $garden, quadrant: Quadrant::DoFirst));
        self::freezeAt('2026-10-04 22:30 UTC');
        self::completeTask(self::createTask('Monday at 00:30, late', dueOn: '2026-10-04', projectId: $garden, quadrant: Quadrant::Schedule));
        self::freezeAt('2026-10-07 07:00 UTC');
        self::completeTask(self::createTask('Today', quadrant: Quadrant::Eliminate));
        $reopened = self::createTask('Reopened');
        self::completeTask($reopened);
        self::getContainer()->get(ReopenTaskHandler::class)(Ulid::fromString($reopened->id));
        self::createTask('Overdue', dueOn: '2026-10-06');
        self::createTask('Due today', dueOn: '2026-10-07');
        self::createTask('Someday');
        self::freezeAt('2026-10-07 08:00 UTC');

        $statistics = $this->statistics();

        self::assertSame('2026-10-07', $statistics->today);
        self::assertEquals(new TotalsView(completed: 5, completedThisWeek: 2, completedThisMonth: 3, open: 4, overdue: 1), $statistics->totals);
        self::assertSame([1, 2], [$statistics->streak, $statistics->bestStreak]);
        self::assertCount(30, $statistics->perDay);
        self::assertSame(['2026-09-08', '2026-10-07'], [$statistics->perDay[0]->date, $statistics->perDay[29]->date]);
        self::assertSame(['2026-09-30' => 1, '2026-10-04' => 1, '2026-10-05' => 1, '2026-10-07' => 1], array_filter(array_combine(
            array_map(static fn (DayCountView $day): string => $day->date, $statistics->perDay),
            array_map(static fn (DayCountView $day): int => $day->count, $statistics->perDay),
        )));
        self::assertSame(['do_first' => 1, 'schedule' => 1, 'delegate' => 0, 'eliminate' => 1, 'unsorted' => 1], $statistics->byQuadrant);
        self::assertEquals([new ProjectCountView($garden, 'Garden', 'moss', 2), new ProjectCountView(null, null, null, 2)], $statistics->byProject);
        self::assertSame([1, 1], [$statistics->onTime->onTime, $statistics->onTime->late]);
    }

    public function testTheWeekStartsOnMonday(): void
    {
        self::freezeAt('2026-10-04 10:00 UTC');
        self::actAsNewUser('Europe/Paris');
        self::completeTask(self::createTask('Sunday'));
        self::freezeAt('2026-10-05 10:00 UTC');
        self::completeTask(self::createTask('Monday'));
        self::freezeAt('2026-10-11 20:00 UTC');

        self::assertSame(1, $this->statistics()->totals->completedThisWeek);
    }

    public function testAnotherUsersTasksAreNeverCounted(): void
    {
        self::freezeAt('2026-10-07 08:00 UTC');
        self::actAsNewUser();
        self::completeTask(self::createTask('Theirs', dueOn: '2026-10-07'));
        self::createTask('Their overdue', dueOn: '2026-10-01');
        self::actAsNewUser();

        $statistics = $this->statistics();

        self::assertEquals(new TotalsView(0, 0, 0, 0, 0), $statistics->totals);
        self::assertSame([0, 0], [$statistics->streak, $statistics->bestStreak]);
        self::assertSame([], array_filter(array_map(static fn (DayCountView $day): int => $day->count, $statistics->perDay)));
        self::assertSame([], $statistics->byProject);
        self::assertSame([0, 0], [$statistics->onTime->onTime, $statistics->onTime->late]);
    }

    private function statistics(): StatisticsView
    {
        return self::getContainer()->get(ShowStatisticsHandler::class)();
    }
}
