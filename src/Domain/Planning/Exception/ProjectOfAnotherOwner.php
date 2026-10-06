<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class ProjectOfAnotherOwner extends InvalidTask
{
    public function __construct()
    {
        parent::__construct('task.foreign_project');
    }
}
