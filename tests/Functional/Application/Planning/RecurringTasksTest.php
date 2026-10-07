<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Planning;

use App\Application\Planning\DeleteTask\DeleteTaskHandler;
use App\Application\Planning\EditTask\EditTask;
use App\Application\Planning\EditTask\EditTaskHandler;
use App\Application\Planning\ReopenTask\ReopenTaskHandler;
use App\Application\Planning\ShowMatrix\ShowMatrixHandler;
use App\Application\Planning\TaskView;
use App\Domain\Gamification\Reward;
use App\Domain\Identity\User;
use App\Domain\Planning\PlanShortcut;
use App\Domain\Planning\Quadrant;
use App\Domain\Planning\Recurrence;
use App\Domain\Planning\RecurrenceUnit;
use App\Domain\Planning\Task;
use App\Domain\Shared\Day;
use App\Tests\Support\ActsAsUser;
use App\Tests\Support\FreezesClock;
use App\Tests\Support\PlansTasks;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;

final class RecurringTasksTest extends KernelTestCase
{
    use ActsAsUser;
    use FreezesClock;
    use PlansTasks;

    private User $user;

    protected function setUp(): void
    {
        self::freezeAt('2026-10-06 08:00 UTC');
        $this->user = self::actAsNewUser();
    }

    #[DataProvider('occurrences')]
    public function testCompletingARecurringTaskSpawnsTheNextOccurrence(?string $planDate, ?string $dueOn, Recurrence $recurrence, ?string $nextPlannedOn, ?string $nextDueOn): void
    {
        $task = self::repeat(self::createTask('Water the ferns', planDate: $planDate, dueOn: $dueOn), $recurrence);

        $completion = self::completeTask($task);

        self::assertTrue($completion->task->done);
        self::assertNull($completion->task->recurrence);
        $next = self::single(self::open());
        self::assertNotSame($task->id, $next->id);
        self::assertSame(
            ['Water the ferns', $nextPlannedOn, $nextDueOn, $recurrence->interval, $recurrence->unit->value, false],
            [$next->title, $next->plannedOn, $next->dueOn, $next->recurrence?->interval, $next->recurrence?->unit, $next->done],
        );
    }

    /** @return iterable<array{?string, ?string, Recurrence, ?string, ?string}> */
    public static function occurrences(): iterable
    {
        $weekly = new Recurrence(1, RecurrenceUnit::Week);

        yield 'planned only' => ['2026-10-06', null, $weekly, '2026-10-13', null];
        yield 'due only' => [null, '2026-10-08', new Recurrence(1, RecurrenceUnit::Month), null, '2026-11-08'];
        yield 'planned and due' => ['2026-10-06', '2026-10-09', $weekly, '2026-10-13', '2026-10-16'];
        yield 'no date' => [null, null, new Recurrence(3, RecurrenceUnit::Day), '2026-10-09', null];
        yield 'overdue anchor advanced past today' => ['2026-09-01', '2026-09-03', $weekly, '2026-10-13', '2026-10-15'];
        yield 'month end clamped' => ['2026-10-31', null, new Recurrence(1, RecurrenceUnit::Month), '2026-11-30', null];
    }

    public function testTodayIsTheUsersDay(): void
    {
        self::freezeAt('2026-10-06 22:30 UTC');
        $task = self::repeat(self::createTask('Water the ferns'), new Recurrence(1, RecurrenceUnit::Day));

        self::completeTask($task);

        self::assertSame('2026-10-08', self::single(self::open())->plannedOn);
    }

    public function testTheNextOccurrenceKeepsProjectNotesAndQuadrantAndGoesLast(): void
    {
        $home = self::createProject('Home');
        self::createTask('Already planted', quadrant: Quadrant::Schedule);
        $task = self::createTask('Water the ferns', PlanShortcut::Today, projectId: $home->id, quadrant: Quadrant::Schedule);
        self::createTask('Planted after', quadrant: Quadrant::Schedule);
        $task = self::repeat($task, new Recurrence(1, RecurrenceUnit::Week), 'The big one too');

        self::completeTask($task);

        $next = self::occurrenceOf('Water the ferns');
        self::assertSame([$home->id, 'The big one too', 'schedule', 3], [$next->projectId, $next->notes, $next->quadrant, $next->rank]);
        self::assertSame(['Already planted', 'Planted after', 'Water the ferns'], self::titles(self::open()));
    }

    public function testReopeningAndCompletingAgainDoesNotDuplicateTheSeries(): void
    {
        $task = self::repeat(self::createTask('Water the ferns', PlanShortcut::Today), new Recurrence(1, RecurrenceUnit::Week));
        self::completeTask($task);

        $reopened = self::getContainer()->get(ReopenTaskHandler::class)(Ulid::fromString($task->id));
        self::assertNull($reopened->recurrence);
        self::assertCount(2, self::open());

        self::completeTask($task);

        $next = self::single(self::open());
        self::assertSame('2026-10-13', $next->plannedOn);
    }

    public function testCompletingTheNextOccurrenceContinuesTheSeriesAndEarnsItsOwnReward(): void
    {
        self::completeTask(self::repeat(self::createTask('Water the ferns', PlanShortcut::Today, quadrant: Quadrant::Schedule), new Recurrence(1, RecurrenceUnit::Week)));
        self::freezeAt('2026-10-13 08:00 UTC');

        $completion = self::completeTask(self::single(self::open()));

        self::assertEquals(new Reward(26), $completion->reward);
        self::assertSame('2026-10-20', self::single(self::open())->plannedOn);
    }

    public function testDeletingTheOpenOccurrenceStopsTheSeries(): void
    {
        self::completeTask(self::repeat(self::createTask('Water the ferns', PlanShortcut::Today), new Recurrence(1, RecurrenceUnit::Week)));

        self::getContainer()->get(DeleteTaskHandler::class)(Ulid::fromString(self::single(self::open())->id));

        self::assertSame([], self::open());
    }

    public function testTheNextOccurrenceBelongsToTheSameOwnerOnly(): void
    {
        self::completeTask(self::repeat(self::createTask('Water the ferns', PlanShortcut::Today), new Recurrence(1, RecurrenceUnit::Week)));
        $next = self::single(self::open());

        $stored = self::getContainer()->get(EntityManagerInterface::class)->find(Task::class, Ulid::fromString($next->id));
        self::assertNotNull($stored);
        self::assertTrue($stored->owner()->id()->equals($this->user->id()));

        self::actAsNewUser();
        self::assertSame([], self::open());
    }

    public function testATaskThatDoesNotRepeatSpawnsNothing(): void
    {
        self::completeTask(self::createTask('Vet', PlanShortcut::Today));

        self::assertSame([], self::open());
    }

    private static function repeat(TaskView $task, Recurrence $recurrence, ?string $notes = null): TaskView
    {
        return self::getContainer()->get(EditTaskHandler::class)(new EditTask(
            Ulid::fromString($task->id),
            $task->title,
            $notes ?? $task->notes,
            null === $task->projectId ? null : Ulid::fromString($task->projectId),
            null === $task->dueOn ? null : Day::of($task->dueOn),
            $recurrence,
        ));
    }

    /**
     * @return list<TaskView>
     */
    private static function open(): array
    {
        return self::getContainer()->get(ShowMatrixHandler::class)();
    }

    /**
     * @param list<TaskView> $tasks
     */
    private static function single(array $tasks): TaskView
    {
        self::assertCount(1, $tasks);

        return $tasks[0];
    }

    private static function occurrenceOf(string $title): TaskView
    {
        $matching = array_values(array_filter(self::open(), static fn (TaskView $task): bool => $task->title === $title));

        return self::single($matching);
    }
}
