<?php

declare(strict_types=1);

namespace App\Application\Gamification\ShowGreenhouse;

use App\Domain\Gamification\Greenhouse\Pot;

final readonly class PotView
{
    private function __construct(
        public int $number,
        public ?string $species,
        public ?string $rarity,
        public int $yield,
        public ?string $plantedAt,
    ) {
    }

    public static function of(Pot $pot): self
    {
        $species = $pot->species();

        return new self(
            $pot->number(),
            $species?->slug,
            $species?->rarity->value,
            $pot->yield(),
            $pot->plantedAt()?->format(\DATE_ATOM),
        );
    }
}
