<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class EmptyTaskTitle extends InvalidTask
{
    public function __construct()
    {
        parent::__construct('task.empty_title');
    }
}
