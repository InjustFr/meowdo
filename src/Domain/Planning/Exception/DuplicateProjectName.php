<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exception;

final class DuplicateProjectName extends InvalidProject
{
    public function __construct(string $name)
    {
        parent::__construct('project.duplicate_name', ['name' => $name]);
    }
}
