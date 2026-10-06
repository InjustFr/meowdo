<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

use App\Domain\Identity\User;

interface PlayerRepository
{
    public function add(Player $player): void;

    public function of(User $owner): Player;
}
