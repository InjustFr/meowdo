<?php

declare(strict_types=1);

namespace App\Application\Gamification\ShowGreenhouse;

use App\Domain\Gamification\Greenhouse\Facilities;
use App\Domain\Gamification\Greenhouse\Facility;

final readonly class FacilityView
{
    private function __construct(
        public string $id,
        public int $level,
        public int $maxLevel,
        public int $effect,
        public ?int $nextEffect,
        public ?int $cost,
    ) {
    }

    public static function of(Facility $facility, Facilities $facilities): self
    {
        $level = $facilities->levelOf($facility);
        $cost = $facility->upgradeCost($level + 1);

        return new self(
            $facility->value,
            $level,
            $facility->maxLevel(),
            $facility->effectAt($level),
            null === $cost ? null : $facility->effectAt($level + 1),
            $cost,
        );
    }
}
