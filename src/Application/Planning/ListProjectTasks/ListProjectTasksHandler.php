<?php

declare(strict_types=1);

namespace App\Application\Planning\ListProjectTasks;

use App\Application\Planning\TaskQueries;
use App\Application\Planning\TaskView;
use App\Application\Planning\Today;
use App\Domain\Planning\ProjectRepository;
use Symfony\Component\Uid\Ulid;

final readonly class ListProjectTasksHandler
{
    public const int DONE_DAYS_SHOWN = 14;

    public function __construct(
        private TaskQueries $tasks,
        private ProjectRepository $projects,
        private Today $today,
    ) {
    }

    /**
     * @return list<TaskView>
     */
    public function __invoke(Ulid $projectId): array
    {
        $doneSince = $this->today->now()->modify(\sprintf('-%d days', self::DONE_DAYS_SHOWN));

        return TaskView::list($this->tasks->ofProject($this->projects->get($projectId), $doneSince));
    }
}
