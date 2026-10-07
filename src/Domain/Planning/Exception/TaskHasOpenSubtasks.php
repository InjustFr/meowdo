<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class TaskHasOpenSubtasks extends InvalidTask
{
    public function __construct(string $title)
    {
        parent::__construct('task.open_subtasks', ['title' => $title]);
    }
}
