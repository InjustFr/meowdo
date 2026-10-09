<?php

declare(strict_types=1);

namespace App\Application\Gamification\ShowGreenhouse;

final readonly class ExpeditionOfferView
{
    public function __construct(
        public int $cost,
        public int $trips,
        public int $speciesLeft,
    ) {
    }
}
