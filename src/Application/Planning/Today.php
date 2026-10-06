<?php

declare(strict_types=1);

namespace App\Application\Planning;

use App\Application\Identity\CurrentUser;
use Psr\Clock\ClockInterface;

final readonly class Today
{
    public function __construct(
        private CurrentUser $currentUser,
        private ClockInterface $clock,
    ) {
    }

    public function date(): \DateTimeImmutable
    {
        return $this->currentUser->get()->today($this->clock->now());
    }

    public function now(): \DateTimeImmutable
    {
        return $this->clock->now();
    }
}
