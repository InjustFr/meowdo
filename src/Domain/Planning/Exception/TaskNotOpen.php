<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class TaskNotOpen extends InvalidTask
{
    public function __construct(string $title)
    {
        parent::__construct('task.not_open', ['title' => $title]);
    }
}
