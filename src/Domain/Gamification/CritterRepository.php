<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

use App\Domain\Identity\User;

interface CritterRepository
{
    public function add(Critter $critter): void;

    public function of(User $owner): Critter;
}
