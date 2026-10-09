<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Greenhouse;

use App\Domain\Planning\Quadrant;
use App\Domain\Planning\Task;

final readonly class DewPolicy
{
    public const int UNSORTED_DEW = 1;

    public function dewFor(Task $task, Greenhouse $greenhouse): DewGain
    {
        return $this->forQuadrant($task->quadrant(), $greenhouse);
    }

    public function forQuadrant(?Quadrant $quadrant, Greenhouse $greenhouse): DewGain
    {
        $rate = $greenhouse->ratePerHourMilli();

        return match ($quadrant) {
            Quadrant::Schedule => self::watered(12, intdiv($rate * $greenhouse->wateringHours(), 1000)),
            Quadrant::DoFirst => self::watered(6, intdiv($rate * $greenhouse->wateringHours(), 2000)),
            Quadrant::Delegate => new DewGain(3, mist: intdiv($rate, 1000)),
            Quadrant::Eliminate => new DewGain(1),
            null => new DewGain(self::UNSORTED_DEW),
        };
    }

    private static function watered(int $base, int $watering): DewGain
    {
        return new DewGain($base + $watering, $watering);
    }
}
