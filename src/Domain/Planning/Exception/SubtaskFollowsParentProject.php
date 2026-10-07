<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class SubtaskFollowsParentProject extends InvalidTask
{
    public function __construct(string $title)
    {
        parent::__construct('task.subtask_follows_parent_project', ['title' => $title]);
    }
}
