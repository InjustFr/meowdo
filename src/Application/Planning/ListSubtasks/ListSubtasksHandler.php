<?php

declare(strict_types=1);

namespace App\Application\Planning\ListSubtasks;

use App\Application\Planning\TaskView;
use App\Domain\Planning\TaskRepository;
use Symfony\Component\Uid\Ulid;

final readonly class ListSubtasksHandler
{
    public function __construct(private TaskRepository $tasks)
    {
    }

    /**
     * @return list<TaskView>
     */
    public function __invoke(Ulid $parentId): array
    {
        return TaskView::list($this->tasks->get($parentId)->subtasks());
    }
}
