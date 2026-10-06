<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class TaskNotDone extends InvalidTask
{
    public function __construct(string $title)
    {
        parent::__construct('task.not_done', ['title' => $title]);
    }
}
