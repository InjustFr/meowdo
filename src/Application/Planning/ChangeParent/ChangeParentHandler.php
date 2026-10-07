<?php

declare(strict_types=1);

namespace App\Application\Planning\ChangeParent;

use App\Application\Planning\TaskView;
use App\Application\Transaction;
use App\Domain\Planning\TaskRepository;

final readonly class ChangeParentHandler
{
    public function __construct(
        private TaskRepository $tasks,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(ChangeParent $command): TaskView
    {
        $task = $this->tasks->get($command->id);
        null === $command->parentId ? $task->promote() : $task->nestUnder($this->tasks->get($command->parentId));
        $this->transaction->commit();

        return TaskView::of($task);
    }
}
