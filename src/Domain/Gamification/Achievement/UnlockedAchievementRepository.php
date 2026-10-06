<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement;

use App\Domain\Identity\User;

interface UnlockedAchievementRepository
{
    public function add(UnlockedAchievement $achievement): void;

    /**
     * @return list<UnlockedAchievement>
     */
    public function of(User $owner): array;
}
