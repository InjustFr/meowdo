<?php

declare(strict_types=1);

namespace App\Application\Gamification;

use App\Domain\Gamification\Herbarium\Specimen;

final readonly class SpecimenView
{
    private function __construct(
        public int $number,
        public string $species,
        public string $rarity,
        public string $collectedAt,
    ) {
    }

    public static function of(Specimen $specimen, int $number): self
    {
        $species = $specimen->species();

        return new self($number, $species->slug, $species->rarity->value, $specimen->collectedAt()->format(\DATE_ATOM));
    }
}
