<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

final readonly class Reward
{
    public function __construct(
        public int $xp,
        public int $coins,
    ) {
    }
}
