<?php

declare(strict_types=1);

namespace App\Application\Planning\CompleteTask;

use App\Application\Planning\Today;
use App\Domain\Planning\Task;
use App\Domain\Planning\TaskRepository;

final readonly class ContinueSeries
{
    public function __construct(
        private TaskRepository $tasks,
        private Today $today,
    ) {
    }

    public function __invoke(Task $completed): void
    {
        $occurrence = $completed->nextOccurrence($this->today->date(), $this->today->now());
        if (null === $occurrence) {
            return;
        }
        $quadrant = $occurrence->quadrant();
        if (null !== $quadrant) {
            $occurrence->classify($quadrant, $this->tasks->nextRankIn($quadrant));
        }
        $this->tasks->add($occurrence);
    }
}
