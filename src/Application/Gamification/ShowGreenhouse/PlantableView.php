<?php

declare(strict_types=1);

namespace App\Application\Gamification\ShowGreenhouse;

use App\Domain\Gamification\Greenhouse\Pot;
use App\Domain\Gamification\Herbarium\Species;

final readonly class PlantableView
{
    private function __construct(
        public string $species,
        public string $rarity,
        public int $yield,
        public ?int $pot,
    ) {
    }

    public static function of(Species $species, ?Pot $pot): self
    {
        return new self($species->slug, $species->rarity->value, $species->rarity->dewYield(), $pot?->number());
    }
}
