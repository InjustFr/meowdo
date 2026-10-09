<?php

declare(strict_types=1);

namespace App\Application\Gamification\ShowGreenhouse;

use App\Application\Identity\CurrentUser;
use App\Domain\Gamification\Greenhouse\DewPolicy;
use App\Domain\Gamification\Greenhouse\Facility;
use App\Domain\Gamification\Greenhouse\Greenhouse;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;
use App\Domain\Gamification\Greenhouse\Pot;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use App\Domain\Gamification\Herbarium\Specimen;
use App\Domain\Gamification\Herbarium\SpecimenRepository;
use App\Domain\Planning\Quadrant;

final readonly class ShowGreenhouseHandler
{
    private const array QUADRANTS = [Quadrant::Schedule, Quadrant::DoFirst, Quadrant::Delegate, Quadrant::Eliminate, null];

    public function __construct(
        private CurrentUser $currentUser,
        private GreenhouseRepository $greenhouses,
        private SpecimenRepository $specimens,
        private DewPolicy $policy,
    ) {
    }

    public function __invoke(): GreenhouseView
    {
        $user = $this->currentUser->get();
        $greenhouse = $this->greenhouses->of($user);
        $specimens = $this->specimens->of($user);

        return new GreenhouseView(
            $greenhouse->dew(),
            $greenhouse->dewGathered(),
            $greenhouse->yieldTenths(),
            $greenhouse->wateringMultiplier(),
            array_map(static fn (Pot $pot): PotView => PotView::of($pot), $greenhouse->pots()),
            Facility::Glasshouse->effectAt(Facility::Glasshouse->maxLevel()),
            array_map(static fn (Facility $facility): FacilityView => FacilityView::of($facility, $greenhouse->facilities()), Facility::cases()),
            new ExpeditionOfferView($greenhouse->expeditionCost(), $greenhouse->expeditions(), \count(SpeciesCatalog::all()) - \count($specimens)),
            $this->plantable($greenhouse, $specimens),
            array_map(fn (?Quadrant $quadrant): TaskDewView => TaskDewView::of($quadrant, $this->policy->forQuadrant($quadrant, $greenhouse)), self::QUADRANTS),
        );
    }

    /**
     * @param list<Specimen> $specimens
     *
     * @return list<PlantableView>
     */
    private function plantable(Greenhouse $greenhouse, array $specimens): array
    {
        $plantable = array_map(static fn (Specimen $specimen): PlantableView => PlantableView::of($specimen->species(), $greenhouse->potOf($specimen->species())), $specimens);
        usort($plantable, static fn (PlantableView $one, PlantableView $other): int => $other->yield <=> $one->yield);

        return $plantable;
    }
}
