<?php

declare(strict_types=1);

namespace App\Application\Gamification\ShowGreenhouse;

final readonly class GreenhouseView
{
    /**
     * @param list<PotView>       $pots
     * @param list<FacilityView>  $facilities
     * @param list<PlantableView> $plantable
     * @param list<TaskDewView>   $taskDew
     */
    public function __construct(
        public int $dew,
        public int $dewGathered,
        public int $yieldTenths,
        public int $wateringMultiplier,
        public array $pots,
        public int $maxPots,
        public array $facilities,
        public ExpeditionOfferView $expedition,
        public array $plantable,
        public array $taskDew,
    ) {
    }
}
