<?php

declare(strict_types=1);

namespace App\Application\Planning\MoveOverdueToToday;

use App\Application\Planning\Today;
use App\Application\Transaction;
use App\Domain\Planning\TaskRepository;

final readonly class MoveOverdueToTodayHandler
{
    public function __construct(
        private TaskRepository $tasks,
        private Today $today,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(): int
    {
        $today = $this->today->date();
        $overdue = $this->tasks->openPlannedBefore($today);
        foreach ($overdue as $task) {
            $task->planFor($today);
        }
        $this->transaction->commit();

        return \count($overdue);
    }
}
