<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Greenhouse;

use App\Domain\Planning\Quadrant;
use App\Domain\Planning\Task;

final readonly class DewPolicy
{
    public const int UNSORTED_DEW = 1;
    private const int HALF = 2;

    public function dewFor(Task $task, Greenhouse $greenhouse): DewGain
    {
        return $this->forQuadrant($task->quadrant(), $greenhouse);
    }

    public function forQuadrant(?Quadrant $quadrant, Greenhouse $greenhouse): DewGain
    {
        $yield = $greenhouse->yieldTenths();
        $watering = $yield * $greenhouse->wateringMultiplier();

        return match ($quadrant) {
            Quadrant::Schedule => new DewGain(12, watering: intdiv($watering, Greenhouse::TENTHS)),
            Quadrant::DoFirst => new DewGain(6, watering: intdiv($watering, self::HALF * Greenhouse::TENTHS)),
            Quadrant::Delegate => new DewGain(3, mist: intdiv($yield, Greenhouse::TENTHS)),
            Quadrant::Eliminate => new DewGain(1),
            null => new DewGain(self::UNSORTED_DEW),
        };
    }
}
