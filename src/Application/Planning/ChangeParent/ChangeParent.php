<?php

declare(strict_types=1);

namespace App\Application\Planning\ChangeParent;

use Symfony\Component\Uid\Ulid;

final readonly class ChangeParent
{
    public function __construct(
        public Ulid $id,
        public ?Ulid $parentId,
    ) {
    }
}
