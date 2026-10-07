<?php

declare(strict_types=1);

namespace App\Application\Planning\AddSubtask;

use Symfony\Component\Uid\Ulid;

final readonly class AddSubtask
{
    public function __construct(
        public Ulid $parentId,
        public string $title,
    ) {
    }
}
