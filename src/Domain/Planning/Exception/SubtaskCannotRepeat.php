<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class SubtaskCannotRepeat extends InvalidTask
{
    public function __construct(string $title)
    {
        parent::__construct('task.subtask_cannot_repeat', ['title' => $title]);
    }
}
