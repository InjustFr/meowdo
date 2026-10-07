<?php

declare(strict_types=1);

namespace App\Application\Gamification;

use App\Domain\Gamification\Herbarium\Specimen;

final readonly class SpecimenView
{
    private function __construct(
        public string $species,
        public string $collectedAt,
    ) {
    }

    public static function of(Specimen $specimen): self
    {
        return new self($specimen->species()->slug, $specimen->collectedAt()->format(\DATE_ATOM));
    }
}
