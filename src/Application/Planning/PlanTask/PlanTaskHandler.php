<?php

declare(strict_types=1);

namespace App\Application\Planning\PlanTask;

use App\Application\Planning\TaskView;
use App\Application\Planning\Today;
use App\Application\Transaction;
use App\Domain\Planning\TaskRepository;

final readonly class PlanTaskHandler
{
    public function __construct(
        private TaskRepository $tasks,
        private Today $today,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(PlanTask $command): TaskView
    {
        $task = $this->tasks->get($command->id);
        $day = $command->when->resolve($this->today->date(), $command->date);
        null === $day ? $task->unplan() : $task->planFor($day);
        $this->transaction->commit();

        return TaskView::of($task);
    }
}
