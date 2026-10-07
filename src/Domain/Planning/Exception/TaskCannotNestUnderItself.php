<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class TaskCannotNestUnderItself extends InvalidTask
{
    public function __construct(string $title)
    {
        parent::__construct('task.cannot_nest_under_itself', ['title' => $title]);
    }
}
