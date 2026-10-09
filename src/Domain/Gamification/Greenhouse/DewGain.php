<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Greenhouse;

final readonly class DewGain
{
    public function __construct(
        public int $amount,
        public int $watering = 0,
        public int $mist = 0,
    ) {
    }

    public function plus(self $other): self
    {
        return new self($this->amount + $other->amount, $this->watering + $other->watering, $this->mist + $other->mist);
    }
}
