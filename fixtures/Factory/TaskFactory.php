<?php

declare(strict_types=1);

namespace App\Fixtures\Factory;

use App\Domain\Planning\Project;
use App\Domain\Planning\Quadrant;
use App\Domain\Planning\Task;
use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/** @extends PersistentObjectFactory<Task> */
final class TaskFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Task::class;
    }

    public function in(Project $project): static
    {
        return $this->afterInstantiate(static fn (Task $task) => $task->fileUnder($project));
    }

    public function plannedOn(\DateTimeImmutable $day): static
    {
        return $this->afterInstantiate(static fn (Task $task) => $task->planFor($day));
    }

    public function dueOn(\DateTimeImmutable $day): static
    {
        return $this->afterInstantiate(static fn (Task $task) => $task->dueBy($day));
    }

    public function classified(Quadrant $quadrant, int $rank = 0): static
    {
        return $this->afterInstantiate(static fn (Task $task) => $task->classify($quadrant, $rank));
    }

    protected function defaults(): array
    {
        return [
            'owner' => UserFactory::new(),
            'title' => rtrim(self::faker()->sentence(3), '.'),
            'now' => new \DateTimeImmutable(),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(Instantiator::namedConstructor('create')->disableHydration());
    }
}
