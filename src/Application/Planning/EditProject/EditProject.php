<?php

declare(strict_types=1);

namespace App\Application\Planning\EditProject;

use App\Domain\Planning\ProjectColor;
use Symfony\Component\Uid\Ulid;

final readonly class EditProject
{
    public function __construct(
        public Ulid $id,
        public string $name,
        public ProjectColor $color,
    ) {
    }
}
