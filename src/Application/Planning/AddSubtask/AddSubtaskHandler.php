<?php

declare(strict_types=1);

namespace App\Application\Planning\AddSubtask;

use App\Application\Planning\TaskView;
use App\Application\Planning\Today;
use App\Application\Transaction;
use App\Domain\Planning\TaskRepository;

final readonly class AddSubtaskHandler
{
    public function __construct(
        private TaskRepository $tasks,
        private Today $today,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(AddSubtask $command): TaskView
    {
        $subtask = $this->tasks->get($command->parentId)->addSubtask($command->title, $this->today->now());
        $this->tasks->add($subtask);
        $this->transaction->commit();

        return TaskView::of($subtask);
    }
}
