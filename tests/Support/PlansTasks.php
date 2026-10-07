<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Application\Planning\CompleteTask\CompleteTaskHandler;
use App\Application\Planning\CompleteTask\CompletionView;
use App\Application\Planning\CreateProject\CreateProject;
use App\Application\Planning\CreateProject\CreateProjectHandler;
use App\Application\Planning\CreateTask\CreateTask;
use App\Application\Planning\CreateTask\CreateTaskHandler;
use App\Application\Planning\ProjectView;
use App\Application\Planning\TaskView;
use App\Domain\Planning\PlanShortcut;
use App\Domain\Planning\ProjectColor;
use App\Domain\Planning\Quadrant;
use App\Domain\Shared\Day;
use Symfony\Component\Uid\Ulid;

trait PlansTasks
{
    protected static function createTask(
        string $title,
        PlanShortcut $plan = PlanShortcut::None,
        ?string $planDate = null,
        ?string $dueOn = null,
        ?string $projectId = null,
        ?Quadrant $quadrant = null,
    ): TaskView {
        return self::getContainer()->get(CreateTaskHandler::class)(new CreateTask(
            $title,
            null,
            null === $projectId ? null : Ulid::fromString($projectId),
            null === $planDate ? $plan : PlanShortcut::Date,
            null === $planDate ? null : Day::of($planDate),
            null === $dueOn ? null : Day::of($dueOn),
            $quadrant,
        ));
    }

    protected static function createProject(string $name, ProjectColor $color = ProjectColor::Honey): ProjectView
    {
        return self::getContainer()->get(CreateProjectHandler::class)(new CreateProject($name, $color));
    }

    protected static function completeTask(TaskView $task): CompletionView
    {
        return self::getContainer()->get(CompleteTaskHandler::class)(Ulid::fromString($task->id));
    }

    /**
     * @param list<TaskView> $tasks
     *
     * @return list<string>
     */
    protected static function titles(array $tasks): array
    {
        return array_map(static fn (TaskView $task): string => $task->title, $tasks);
    }
}
