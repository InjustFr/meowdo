<?php

declare(strict_types=1);

namespace App\Application\Planning\ListProjects;

use App\Application\Planning\ProjectView;
use App\Application\Planning\TaskQueries;
use App\Domain\Planning\Project;
use App\Domain\Planning\ProjectRepository;

final readonly class ListProjectsHandler
{
    public function __construct(
        private ProjectRepository $projects,
        private TaskQueries $tasks,
    ) {
    }

    /**
     * @return list<ProjectView>
     */
    public function __invoke(): array
    {
        $counts = $this->tasks->openCountByProject();

        return array_map(
            static fn (Project $project): ProjectView => ProjectView::of($project, $counts[(string) $project->id()] ?? 0),
            $this->projects->all(),
        );
    }
}
