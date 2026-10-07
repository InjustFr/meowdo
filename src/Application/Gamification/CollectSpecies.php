<?php

declare(strict_types=1);

namespace App\Application\Gamification;

use App\Domain\Gamification\Herbarium\Species;
use App\Domain\Gamification\Herbarium\SpeciesDraw;
use App\Domain\Gamification\Herbarium\Specimen;
use App\Domain\Gamification\Herbarium\SpecimenRepository;
use App\Domain\Identity\User;
use Psr\Clock\ClockInterface;

final readonly class CollectSpecies
{
    public function __construct(
        private SpecimenRepository $specimens,
        private SpeciesDraw $draw,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @return list<Species>
     */
    public function __invoke(User $owner, int $levelsCrossed): array
    {
        $collected = array_map(static fn (Specimen $specimen): Species => $specimen->species(), $this->specimens->of($owner));
        $drawn = $this->draw->draw($levelsCrossed, $collected);
        foreach ($drawn as $species) {
            $this->specimens->add(Specimen::collect($owner, $species, $this->clock->now()));
        }

        return $drawn;
    }
}
