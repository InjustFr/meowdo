<?php

declare(strict_types=1);

namespace App\Application\Gamification\ListAchievements;

final readonly class AchievementView
{
    public function __construct(
        public string $id,
        public ?string $unlockedAt,
    ) {
    }
}
