<?php

declare(strict_types=1);

namespace App\Application\Planning\ShowMatrix;

use App\Application\Planning\TaskQueries;
use App\Application\Planning\TaskView;

final readonly class ShowMatrixHandler
{
    public function __construct(private TaskQueries $tasks)
    {
    }

    /**
     * @return list<TaskView>
     */
    public function __invoke(): array
    {
        return TaskView::list($this->tasks->open());
    }
}
