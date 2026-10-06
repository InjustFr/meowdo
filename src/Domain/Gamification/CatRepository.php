<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

use App\Domain\Identity\User;

interface CatRepository
{
    public function add(Cat $cat): void;

    public function of(User $owner): Cat;
}
