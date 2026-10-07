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

    public function plus(self $other): self
    {
        return new self($this->xp + $other->xp, $this->coins + $other->coins);
    }
}
