<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Domain\Gamification\Coat;

final readonly class CoatPayload
{
    public function __construct(
        public Coat $coat = Coat::Ginger,
    ) {
    }
}
