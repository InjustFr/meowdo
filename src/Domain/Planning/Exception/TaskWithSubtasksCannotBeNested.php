<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class TaskWithSubtasksCannotBeNested extends InvalidTask
{
    public function __construct(string $title)
    {
        parent::__construct('task.with_subtasks_cannot_be_nested', ['title' => $title]);
    }
}
