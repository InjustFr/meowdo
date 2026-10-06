<?php

declare(strict_types=1);

namespace App\Application\Planning\EditTask;

use Symfony\Component\Uid\Ulid;

final readonly class EditTask
{
    public function __construct(
        public Ulid $id,
        public string $title,
        public ?string $notes,
        public ?Ulid $projectId,
        public ?\DateTimeImmutable $dueOn,
    ) {
    }
}
