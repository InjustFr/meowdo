<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use Symfony\Component\Uid\Ulid;

final readonly class ParentPayload
{
    public function __construct(
        public ?Ulid $parentId = null,
    ) {
    }
}
