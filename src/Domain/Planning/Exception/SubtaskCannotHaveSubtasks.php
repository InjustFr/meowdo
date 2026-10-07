<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class SubtaskCannotHaveSubtasks extends InvalidTask
{
    public function __construct(string $title)
    {
        parent::__construct('task.subtask_cannot_have_subtasks', ['title' => $title]);
    }
}
