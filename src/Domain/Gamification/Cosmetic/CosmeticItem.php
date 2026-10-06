<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Cosmetic;

final readonly class CosmeticItem
{
    public function __construct(
        public string $slug,
        public Slot $slot,
        public int $price,
        public int $minLevel = 1,
    ) {
    }
}
