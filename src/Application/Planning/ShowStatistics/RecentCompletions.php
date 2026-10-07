<?php

declare(strict_types=1);

namespace App\Application\Planning\ShowStatistics;

use App\Domain\Planning\Quadrant;
use App\Domain\Planning\Task;
use App\Domain\Shared\Day;

final readonly class RecentCompletions
{
    public const string UNSORTED = 'unsorted';

    /**
     * @param list<Task> $tasks
     */
    public function __construct(
        private array $tasks,
        private \DateTimeZone $zone,
    ) {
    }

    /**
     * @return list<DayCountView>
     */
    public function perDay(\DateTimeImmutable $first, \DateTimeImmutable $last): array
    {
        $counts = [];
        for ($day = Day::normalize($first); $day <= $last; $day = $day->modify('+1 day')) {
            $counts[(string) Day::format($day)] = 0;
        }
        foreach ($this->tasks as $task) {
            $day = (string) Day::format($this->completedOn($task));
            if (isset($counts[$day])) {
                ++$counts[$day];
            }
        }

        return array_map(static fn (string $date, int $count): DayCountView => new DayCountView($date, $count), array_keys($counts), array_values($counts));
    }

    /**
     * @return array<string, int>
     */
    public function byQuadrant(): array
    {
        $counts = [...array_fill_keys(array_map(static fn (Quadrant $quadrant): string => $quadrant->value, Quadrant::cases()), 0), self::UNSORTED => 0];
        foreach ($this->tasks as $task) {
            ++$counts[$task->quadrant()->value ?? self::UNSORTED];
        }

        return $counts;
    }

    /**
     * @return list<ProjectCountView>
     */
    public function byProject(): array
    {
        $counts = [];
        foreach ($this->tasks as $task) {
            $project = $task->project();
            $key = null === $project ? '' : (string) $project->id();
            $counts[$key] = new ProjectCountView(
                null === $project ? null : (string) $project->id(),
                $project?->name(),
                $project?->color()->value,
                ($counts[$key]->count ?? 0) + 1,
            );
        }
        $counts = array_values($counts);
        usort($counts, static fn (ProjectCountView $a, ProjectCountView $b): int => [$b->count, null === $a->name, $a->name] <=> [$a->count, null === $b->name, $b->name]);

        return $counts;
    }

    public function onTime(): OnTimeView
    {
        $onTime = 0;
        $late = 0;
        foreach ($this->tasks as $task) {
            $dueOn = $task->dueOn();
            if (null === $dueOn) {
                continue;
            }
            $this->completedOn($task) <= $dueOn ? ++$onTime : ++$late;
        }

        return new OnTimeView($onTime, $late);
    }

    private function completedOn(Task $task): \DateTimeImmutable
    {
        return Day::at($task->completedAt() ?? $task->createdAt(), $this->zone);
    }
}
