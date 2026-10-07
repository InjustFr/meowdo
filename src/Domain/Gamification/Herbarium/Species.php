<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Herbarium;

final readonly class Species
{
    public string $slug;

    public function __construct(
        public string $scientificName,
        public Rarity $rarity,
    ) {
        $this->slug = strtolower(str_replace(' ', '-', $scientificName));
    }
}
