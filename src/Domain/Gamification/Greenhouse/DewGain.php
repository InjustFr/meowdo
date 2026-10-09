<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Greenhouse;

final readonly class DewGain
{
    public int $amount;

    public function __construct(
        public int $base,
        public int $watering = 0,
        public int $mist = 0,
    ) {
        $this->amount = $base + $watering + $mist;
    }

    public function plus(self $other): self
    {
        return new self($this->base + $other->base, $this->watering + $other->watering, $this->mist + $other->mist);
    }
}
