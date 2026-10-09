<?php

declare(strict_types=1);

namespace App\Application\Gamification\CollectDew;

final readonly class CollectedDew
{
    public function __construct(
        public int $collected,
        public int $dew,
    ) {
    }
}
