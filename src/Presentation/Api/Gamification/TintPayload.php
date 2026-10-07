<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Domain\Gamification\Tint;

final readonly class TintPayload
{
    public function __construct(
        public Tint $tint = Tint::Sprout,
    ) {
    }
}
