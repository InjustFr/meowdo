<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class TaskAlreadyDone extends InvalidTask
{
    public function __construct(string $title)
    {
        parent::__construct('task.already_done', ['title' => $title]);
    }
}
