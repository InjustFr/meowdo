<?php

declare(strict_types=1);

namespace App\Application\Planning\EditTask;

use App\Application\Planning\TaskView;
use App\Application\Transaction;
use App\Domain\Planning\ProjectRepository;
use App\Domain\Planning\TaskRepository;

final readonly class EditTaskHandler
{
    public function __construct(
        private TaskRepository $tasks,
        private ProjectRepository $projects,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(EditTask $command): TaskView
    {
        $task = $this->tasks->get($command->id);
        $task->rename($command->title);
        $task->describe($command->notes);
        null === $command->projectId ? $task->detach() : $task->fileUnder($this->projects->get($command->projectId));
        null === $command->dueOn ? $task->clearDeadline() : $task->dueBy($command->dueOn);
        $this->transaction->commit();

        return TaskView::of($task);
    }
}
