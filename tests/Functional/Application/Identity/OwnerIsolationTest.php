<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Identity;

use App\Application\Planning\ClassifyTask\ClassifyTask;
use App\Application\Planning\ClassifyTask\ClassifyTaskHandler;
use App\Application\Planning\CompleteTask\CompleteTaskHandler;
use App\Application\Planning\DeleteProject\DeleteProjectHandler;
use App\Application\Planning\DeleteTask\DeleteTaskHandler;
use App\Application\Planning\EditProject\EditProject;
use App\Application\Planning\EditProject\EditProjectHandler;
use App\Application\Planning\EditTask\EditTask;
use App\Application\Planning\EditTask\EditTaskHandler;
use App\Application\Planning\ListInboxTasks\ListInboxTasksHandler;
use App\Application\Planning\ListProjects\ListProjectsHandler;
use App\Application\Planning\ListProjectTasks\ListProjectTasksHandler;
use App\Application\Planning\ListTodayTasks\ListTodayTasksHandler;
use App\Application\Planning\ListUpcomingTasks\ListUpcomingTasksHandler;
use App\Application\Planning\MoveOverdueToToday\MoveOverdueToTodayHandler;
use App\Application\Planning\PlanTask\PlanTask;
use App\Application\Planning\PlanTask\PlanTaskHandler;
use App\Application\Planning\ProjectView;
use App\Application\Planning\ReopenTask\ReopenTaskHandler;
use App\Application\Planning\ReorderQuadrant\ReorderQuadrant;
use App\Application\Planning\ReorderQuadrant\ReorderQuadrantHandler;
use App\Application\Planning\ShowMatrix\ShowMatrixHandler;
use App\Application\Planning\TaskView;
use App\Domain\Planning\PlanShortcut;
use App\Domain\Planning\ProjectColor;
use App\Domain\Planning\Quadrant;
use App\Domain\Shared\Exception\NotFound;
use App\Tests\Support\ActsAsUser;
use App\Tests\Support\FreezesClock;
use App\Tests\Support\PlansTasks;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;

final class OwnerIsolationTest extends KernelTestCase
{
    use ActsAsUser;
    use FreezesClock;
    use PlansTasks;

    private TaskView $task;
    private ProjectView $project;

    protected function setUp(): void
    {
        self::freezeAt('2026-10-06 08:00 UTC');
        self::actAsNewUser();
        $this->project = self::createProject('Secret project');
        $this->task = self::createTask('Secret task', planDate: '2026-10-01', projectId: $this->project->id, quadrant: Quadrant::DoFirst);
        self::createTask('Secret inbox', PlanShortcut::Tomorrow);
        self::actAsNewUser();
    }

    /**
     * @param \Closure(Ulid, Ulid): mixed $action
     */
    #[DataProvider('actionsOnAnotherUsersData')]
    public function testAnotherUsersDataIsNotFound(\Closure $action, string $subject): void
    {
        try {
            $action(Ulid::fromString($this->task->id), Ulid::fromString($this->project->id));
        } catch (NotFound $notFound) {
            self::assertSame($subject, $notFound->parameters()['subject']);

            return;
        }

        self::fail('Another user\'s data was reachable.');
    }

    /** @return iterable<array{\Closure(Ulid, Ulid): mixed, string}> */
    public static function actionsOnAnotherUsersData(): iterable
    {
        yield 'complete task' => [static fn (Ulid $task): mixed => self::getContainer()->get(CompleteTaskHandler::class)($task), 'task'];
        yield 'reopen task' => [static fn (Ulid $task): mixed => self::getContainer()->get(ReopenTaskHandler::class)($task), 'task'];
        yield 'edit task' => [static fn (Ulid $task): mixed => self::getContainer()->get(EditTaskHandler::class)(new EditTask($task, 'Mine now', null, null, null)), 'task'];
        yield 'plan task' => [static fn (Ulid $task): mixed => self::getContainer()->get(PlanTaskHandler::class)(new PlanTask($task, PlanShortcut::Today)), 'task'];
        yield 'classify task' => [static fn (Ulid $task): mixed => self::getContainer()->get(ClassifyTaskHandler::class)(new ClassifyTask($task, Quadrant::Eliminate)), 'task'];
        yield 'reorder task' => [static function (Ulid $task): void {
            self::getContainer()->get(ReorderQuadrantHandler::class)(new ReorderQuadrant(Quadrant::Eliminate, [$task]));
        }, 'task'];
        yield 'delete task' => [static function (Ulid $task): void {
            self::getContainer()->get(DeleteTaskHandler::class)($task);
        }, 'task'];
        yield 'list project tasks' => [static fn (Ulid $task, Ulid $project): mixed => self::getContainer()->get(ListProjectTasksHandler::class)($project), 'project'];
        yield 'edit project' => [static fn (Ulid $task, Ulid $project): mixed => self::getContainer()->get(EditProjectHandler::class)(new EditProject($project, 'Mine now', ProjectColor::Sky)), 'project'];
        yield 'delete project' => [static function (Ulid $task, Ulid $project): void {
            self::getContainer()->get(DeleteProjectHandler::class)($project);
        }, 'project'];
        yield 'file a task under it' => [static fn (Ulid $task, Ulid $project): mixed => self::createTask('Mine', projectId: (string) $project), 'project'];
    }

    public function testListsShowOnlyOwnData(): void
    {
        self::createTask('Mine', PlanShortcut::Today);

        self::assertSame([], self::getContainer()->get(ListProjectsHandler::class)());
        self::assertSame(['Mine'], self::titles(self::getContainer()->get(ShowMatrixHandler::class)()));
        self::assertSame(['Mine'], self::titles(self::getContainer()->get(ListInboxTasksHandler::class)()));
        self::assertSame(['Mine'], self::titles(self::getContainer()->get(ListUpcomingTasksHandler::class)()->tasks));
        $today = self::getContainer()->get(ListTodayTasksHandler::class)();
        self::assertSame([[], []], [self::titles($today->earlier), self::titles($today->done)]);
        self::assertSame(0, self::getContainer()->get(MoveOverdueToTodayHandler::class)());
    }

    public function testMyNewTaskGoesFirstInAQuadrantOnlyAnotherUserUses(): void
    {
        self::assertSame(0, self::createTask('Mine', quadrant: Quadrant::DoFirst)->rank);
    }
}
