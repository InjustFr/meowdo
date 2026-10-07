<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class InvalidRecurrence extends InvalidTask
{
    public function __construct(int $interval)
    {
        parent::__construct('task.invalid_recurrence', ['interval' => $interval]);
    }
}
