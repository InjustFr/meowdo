<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Herbarium;

use App\Domain\Identity\User;

interface SpecimenRepository
{
    public function add(Specimen $specimen): void;

    /**
     * @return list<Specimen>
     */
    public function of(User $owner): array;

    public function ofSpecies(User $owner, Species $species): ?Specimen;
}
