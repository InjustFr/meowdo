<?php

declare(strict_types=1);

namespace App\Application\Gamification\PlantMoss;

final readonly class PlantMoss
{
    public function __construct(
        public int $pot,
        public string $species,
    ) {
    }
}
