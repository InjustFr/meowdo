<?php

declare(strict_types=1);

namespace App\Application\Planning;

use App\Domain\Planning\Project;

final readonly class ProjectView
{
    private function __construct(
        public string $id,
        public string $name,
        public string $color,
        public int $openTasks,
    ) {
    }

    public static function of(Project $project, int $openTasks = 0): self
    {
        return new self((string) $project->id(), $project->name(), $project->color()->value, $openTasks);
    }
}
