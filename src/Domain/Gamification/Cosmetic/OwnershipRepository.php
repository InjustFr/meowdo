<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Cosmetic;

use App\Domain\Identity\User;

interface OwnershipRepository
{
    public function add(Ownership $ownership): void;

    public function find(User $owner, string $slug): ?Ownership;

    /**
     * @return list<string>
     */
    public function slugsOf(User $owner): array;
}
