<?php

declare(strict_types=1);

namespace App\Application\Planning\ListUpcomingTasks;

use App\Application\Planning\TaskView;

final readonly class UpcomingView
{
    /**
     * @param list<string>   $days
     * @param list<TaskView> $tasks
     */
    public function __construct(
        public string $today,
        public array $days,
        public array $tasks,
    ) {
    }
}
