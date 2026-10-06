<?php

declare(strict_types=1);

namespace App\Application\Planning\CreateProject;

use App\Domain\Planning\ProjectColor;

final readonly class CreateProject
{
    public function __construct(
        public string $name,
        public ProjectColor $color,
    ) {
    }
}
