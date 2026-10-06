<?php

declare(strict_types=1);

namespace App\Application\Planning\ListTodayTasks;

use App\Application\Identity\CurrentUser;
use App\Application\Planning\TaskQueries;
use App\Application\Planning\TaskView;
use App\Application\Planning\Today;
use App\Domain\Planning\Task;
use App\Domain\Shared\Day;

final readonly class ListTodayTasksHandler
{
    public function __construct(
        private TaskQueries $tasks,
        private Today $today,
        private CurrentUser $currentUser,
    ) {
    }

    public function __invoke(): TodayView
    {
        $today = $this->today->date();
        $zone = $this->currentUser->get()->zone();
        $open = $this->tasks->openForDay($today);
        $isEarlier = static fn (Task $task): bool => null !== $task->plannedOn() && $task->plannedOn() < $today
            && (null === $task->dueOn() || $task->dueOn() > $today);

        return new TodayView(
            (string) Day::format($today),
            TaskView::list(array_values(array_filter($open, static fn (Task $task): bool => !$isEarlier($task)))),
            TaskView::list(array_values(array_filter($open, $isEarlier))),
            TaskView::list($this->tasks->completedBetween(Day::startOf($today, $zone), Day::startOf($today->modify('+1 day'), $zone))),
        );
    }
}
