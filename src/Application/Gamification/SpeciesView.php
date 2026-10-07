<?php

declare(strict_types=1);

namespace App\Application\Gamification;

use App\Domain\Gamification\Herbarium\Species;

final readonly class SpeciesView
{
    private function __construct(
        public string $slug,
        public string $rarity,
    ) {
    }

    public static function of(Species $species): self
    {
        return new self($species->slug, $species->rarity->value);
    }
}
