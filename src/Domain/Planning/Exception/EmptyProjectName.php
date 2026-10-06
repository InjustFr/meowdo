<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class EmptyProjectName extends InvalidProject
{
    public function __construct()
    {
        parent::__construct('project.empty_name');
    }
}
