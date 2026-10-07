<?php

declare(strict_types=1);

namespace App\Application\Planning;

use App\Domain\Planning\Task;
use App\Domain\Shared\Day;

final readonly class TaskView
{
    private function __construct(
        public string $id,
        public string $title,
        public ?string $notes,
        public ?string $projectId,
        public ?string $plannedOn,
        public ?string $dueOn,
        public ?string $quadrant,
        public int $rank,
        public ?RecurrenceView $recurrence,
        public bool $done,
        public ?string $completedAt,
        public string $createdAt,
    ) {
    }

    public static function of(Task $task): self
    {
        return new self(
            (string) $task->id(),
            $task->title(),
            $task->notes(),
            null === $task->project() ? null : (string) $task->project()->id(),
            Day::format($task->plannedOn()),
            Day::format($task->dueOn()),
            $task->quadrant()?->value,
            $task->rank(),
            RecurrenceView::of($task->recurrence()),
            $task->isDone(),
            $task->completedAt()?->format(\DATE_ATOM),
            $task->createdAt()->format(\DATE_ATOM),
        );
    }

    /**
     * @param list<Task> $tasks
     *
     * @return list<self>
     */
    public static function list(array $tasks): array
    {
        return array_map(self::of(...), $tasks);
    }
}
