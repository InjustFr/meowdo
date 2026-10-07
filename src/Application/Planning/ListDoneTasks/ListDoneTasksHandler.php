<?php

declare(strict_types=1);

namespace App\Application\Planning\ListDoneTasks;

use App\Application\Identity\CurrentUser;
use App\Application\Planning\TaskQueries;
use App\Application\Planning\TaskView;
use App\Application\Planning\Today;
use App\Domain\Shared\Day;

final readonly class ListDoneTasksHandler
{
    public const int DAYS = 30;

    public function __construct(
        private TaskQueries $tasks,
        private Today $today,
        private CurrentUser $currentUser,
    ) {
    }

    public function __invoke(?\DateTimeImmutable $before = null): DoneView
    {
        $zone = $this->currentUser->get()->zone();
        $until = null === $before ? $this->today->date()->modify('+1 day') : Day::normalize($before);
        $from = $until->modify(\sprintf('-%d days', self::DAYS));

        $days = [];
        foreach ($this->tasks->completedBetween(Day::startOf($from, $zone), Day::startOf($until, $zone)) as $task) {
            $days[(string) Day::format(Day::at($task->completedAt() ?? $from, $zone))][] = TaskView::of($task);
        }
        $older = $this->tasks->lastCompletedBefore(Day::startOf($from, $zone))?->completedAt();

        return new DoneView(
            array_map(static fn (string $date, array $tasks): DoneDayView => new DoneDayView($date, $tasks), array_keys($days), array_values($days)),
            null === $older ? null : Day::format(Day::at($older, $zone)->modify('+1 day')),
        );
    }
}
