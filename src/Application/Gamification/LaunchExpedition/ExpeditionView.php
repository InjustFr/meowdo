<?php

declare(strict_types=1);

namespace App\Application\Gamification\LaunchExpedition;

use App\Application\Gamification\SpeciesView;

final readonly class ExpeditionView
{
    public function __construct(
        public SpeciesView $species,
        public int $nextCost,
    ) {
    }
}
