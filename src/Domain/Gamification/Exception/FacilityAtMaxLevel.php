<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

use App\Domain\Gamification\Greenhouse\Facility;

final class FacilityAtMaxLevel extends InvalidGameAction
{
    public function __construct(Facility $facility)
    {
        parent::__construct('game.facility_at_max_level', ['facility' => $facility->value]);
    }
}
