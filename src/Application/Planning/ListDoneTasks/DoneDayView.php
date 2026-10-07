<?php

declare(strict_types=1);

namespace App\Application\Planning\ListDoneTasks;

use App\Application\Planning\TaskView;

final readonly class DoneDayView
{
    /**
     * @param list<TaskView> $tasks
     */
    public function __construct(
        public string $date,
        public array $tasks,
    ) {
    }
}
