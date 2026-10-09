<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Greenhouse;

use App\Domain\Identity\User;

interface GreenhouseRepository
{
    public function add(Greenhouse $greenhouse): void;

    public function of(User $owner): Greenhouse;
}
