<?php

declare(strict_types=1);

namespace App\Application\Planning\CreateTask;

use App\Application\Identity\CurrentUser;
use App\Application\Planning\TaskView;
use App\Application\Planning\Today;
use App\Application\Transaction;
use App\Domain\Planning\ProjectRepository;
use App\Domain\Planning\Task;
use App\Domain\Planning\TaskRepository;

final readonly class CreateTaskHandler
{
    public function __construct(
        private TaskRepository $tasks,
        private ProjectRepository $projects,
        private CurrentUser $currentUser,
        private Today $today,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(CreateTask $command): TaskView
    {
        $task = Task::create($this->currentUser->get(), $command->title, $this->today->now());
        $task->describe($command->notes);
        if (null !== $command->projectId) {
            $task->fileUnder($this->projects->get($command->projectId));
        }
        $plannedOn = $command->plan->resolve($this->today->date(), $command->planDate);
        if (null !== $plannedOn) {
            $task->planFor($plannedOn);
        }
        if (null !== $command->dueOn) {
            $task->dueBy($command->dueOn);
        }
        if (null !== $command->quadrant) {
            $task->classify($command->quadrant, $this->tasks->nextRankIn($command->quadrant));
        }

        $this->tasks->add($task);
        $this->transaction->commit();

        return TaskView::of($task);
    }
}
