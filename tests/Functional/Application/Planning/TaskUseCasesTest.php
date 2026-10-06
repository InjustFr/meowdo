<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Planning;

use App\Application\Planning\CreateTask\CreateTask;
use App\Application\Planning\CreateTask\CreateTaskHandler;
use App\Application\Planning\DeleteTask\DeleteTaskHandler;
use App\Application\Planning\EditTask\EditTask;
use App\Application\Planning\EditTask\EditTaskHandler;
use App\Application\Planning\PlanTask\PlanTask;
use App\Application\Planning\PlanTask\PlanTaskHandler;
use App\Application\Planning\ReopenTask\ReopenTaskHandler;
use App\Application\Planning\ShowMatrix\ShowMatrixHandler;
use App\Application\Planning\TaskView;
use App\Domain\Planning\Exception\EmptyTaskTitle;
use App\Domain\Planning\Exception\MissingPlanDate;
use App\Domain\Planning\Exception\TaskAlreadyDone;
use App\Domain\Planning\Exception\TaskNotDone;
use App\Domain\Planning\PlanShortcut;
use App\Domain\Planning\Quadrant;
use App\Domain\Shared\Day;
use App\Tests\Support\ActsAsUser;
use App\Tests\Support\FreezesClock;
use App\Tests\Support\PlansTasks;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;

final class TaskUseCasesTest extends KernelTestCase
{
    use ActsAsUser;
    use FreezesClock;
    use PlansTasks;

    protected function setUp(): void
    {
        self::freezeAt('2026-10-06 08:00 UTC');
        self::actAsNewUser();
    }

    public function testCreateATaskWithEverything(): void
    {
        $project = self::createProject('Home');

        $task = self::getContainer()->get(CreateTaskHandler::class)(new CreateTask(
            "  Book   the vet\n",
            ' Bring the booklet ',
            Ulid::fromString($project->id),
            PlanShortcut::Tomorrow,
            null,
            Day::of('2026-10-09'),
            Quadrant::DoFirst,
        ));

        self::assertSame(
            ['Book the vet', 'Bring the booklet', $project->id, '2026-10-07', '2026-10-09', 'do_first', 0, false, null],
            [$task->title, $task->notes, $task->projectId, $task->plannedOn, $task->dueOn, $task->quadrant, $task->rank, $task->done, $task->completedAt],
        );
    }

    public function testANewTaskGoesLastInItsQuadrant(): void
    {
        self::createTask('First', quadrant: Quadrant::Schedule);
        self::createTask('Second', quadrant: Quadrant::Schedule);
        self::createTask('Elsewhere', quadrant: Quadrant::DoFirst);

        self::assertSame(2, self::createTask('Third', quadrant: Quadrant::Schedule)->rank);
    }

    public function testTitleIsRequired(): void
    {
        $this->expectExceptionObject(new EmptyTaskTitle());

        self::createTask(" \n ");
    }

    #[DataProvider('plans')]
    public function testPlanTask(PlanShortcut $when, ?string $date, ?string $plannedOn): void
    {
        $task = self::createTask('Vet', PlanShortcut::Today);

        self::assertSame($plannedOn, $this->plan($task, $when, $date)->plannedOn);
    }

    /** @return iterable<array{PlanShortcut, ?string, ?string}> */
    public static function plans(): iterable
    {
        yield 'today' => [PlanShortcut::Today, null, '2026-10-06'];
        yield 'tomorrow' => [PlanShortcut::Tomorrow, null, '2026-10-07'];
        yield 'next week' => [PlanShortcut::NextWeek, null, '2026-10-12'];
        yield 'picked date' => [PlanShortcut::Date, '2026-11-02', '2026-11-02'];
        yield 'not planned' => [PlanShortcut::None, null, null];
    }

    public function testPlanningResolvesInTheUsersTimezone(): void
    {
        self::freezeAt('2026-10-11 22:30 UTC');
        $task = self::createTask('Vet');

        self::assertSame('2026-10-12', $this->plan($task, PlanShortcut::Today)->plannedOn);
        self::assertSame('2026-10-19', $this->plan($task, PlanShortcut::NextWeek)->plannedOn);
    }

    public function testPickedDateIsRequired(): void
    {
        $task = self::createTask('Vet');

        $this->expectExceptionObject(new MissingPlanDate());

        $this->plan($task, PlanShortcut::Date);
    }

    public function testEditTask(): void
    {
        $home = self::createProject('Home');
        $work = self::createProject('Work');
        $task = self::createTask('Vet', PlanShortcut::Today, dueOn: '2026-10-09', projectId: $home->id, quadrant: Quadrant::Delegate);

        $moved = $this->edit($task, 'Vet appointment', 'Morning', $work->id, '2026-10-20');
        self::assertSame(['Vet appointment', 'Morning', $work->id, '2026-10-20', '2026-10-06', 'delegate'], [$moved->title, $moved->notes, $moved->projectId, $moved->dueOn, $moved->plannedOn, $moved->quadrant]);

        $cleared = $this->edit($task, 'Vet appointment', '  ', null, null);
        self::assertSame([null, null, null], [$cleared->notes, $cleared->projectId, $cleared->dueOn]);
    }

    public function testCompleteReopenAndDelete(): void
    {
        $task = self::createTask('Vet');
        $id = Ulid::fromString($task->id);

        self::assertTrue(self::completeTask($task)->task->done);

        $reopened = self::getContainer()->get(ReopenTaskHandler::class)($id);
        self::assertFalse($reopened->done);
        self::assertNull($reopened->completedAt);

        self::getContainer()->get(DeleteTaskHandler::class)($id);
        self::assertSame([], self::getContainer()->get(ShowMatrixHandler::class)());
    }

    public function testCompletingADoneTaskThrows(): void
    {
        $task = self::createTask('Vet');
        self::completeTask($task);

        $this->expectExceptionObject(new TaskAlreadyDone('Vet'));

        self::completeTask($task);
    }

    public function testReopeningAnOpenTaskThrows(): void
    {
        $task = self::createTask('Vet');

        $this->expectExceptionObject(new TaskNotDone('Vet'));

        self::getContainer()->get(ReopenTaskHandler::class)(Ulid::fromString($task->id));
    }

    private function plan(TaskView $task, PlanShortcut $when, ?string $date = null): TaskView
    {
        return self::getContainer()->get(PlanTaskHandler::class)(new PlanTask(Ulid::fromString($task->id), $when, null === $date ? null : Day::of($date)));
    }

    private function edit(TaskView $task, string $title, ?string $notes, ?string $projectId, ?string $dueOn): TaskView
    {
        return self::getContainer()->get(EditTaskHandler::class)(new EditTask(
            Ulid::fromString($task->id),
            $title,
            $notes,
            null === $projectId ? null : Ulid::fromString($projectId),
            null === $dueOn ? null : Day::of($dueOn),
        ));
    }
}
