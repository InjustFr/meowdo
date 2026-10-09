<?php

declare(strict_types=1);

namespace App\Application\Gamification\ShowGreenhouse;

use App\Domain\Gamification\Greenhouse\DewGain;
use App\Domain\Planning\Quadrant;

final readonly class TaskDewView
{
    private function __construct(
        public ?string $quadrant,
        public int $base,
        public int $watering,
        public int $mist,
        public int $amount,
    ) {
    }

    public static function of(?Quadrant $quadrant, DewGain $gain): self
    {
        return new self($quadrant?->value, $gain->amount - $gain->watering, $gain->watering, $gain->mist, $gain->amount);
    }
}
