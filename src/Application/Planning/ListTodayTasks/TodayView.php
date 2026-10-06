<?php

declare(strict_types=1);

namespace App\Application\Planning\ListTodayTasks;

use App\Application\Planning\TaskView;

final readonly class TodayView
{
    /**
     * @param list<TaskView> $today
     * @param list<TaskView> $earlier
     * @param list<TaskView> $done
     */
    public function __construct(
        public string $date,
        public array $today,
        public array $earlier,
        public array $done,
    ) {
    }
}
