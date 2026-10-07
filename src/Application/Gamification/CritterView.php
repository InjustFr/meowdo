<?php

declare(strict_types=1);

namespace App\Application\Gamification;

use App\Domain\Gamification\Critter;
use App\Domain\Gamification\CritterMood;

final readonly class CritterView
{
    /**
     * @param array{hat: ?string, neckwear: ?string, toy: ?string, backdrop: ?string} $outfit
     */
    private function __construct(
        public string $name,
        public string $tint,
        public string $mood,
        public array $outfit,
    ) {
    }

    public static function of(Critter $critter, CritterMood $mood): self
    {
        return new self($critter->name(), $critter->tint()->value, $mood->value, $critter->outfit());
    }
}
