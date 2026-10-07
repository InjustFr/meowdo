<?php

declare(strict_types=1);

namespace App\Application\Gamification;

final readonly class PlayerView
{
    /**
     * @param list<string> $newAchievements
     */
    public function __construct(
        public string $displayName,
        public int $xp,
        public int $coins,
        public int $level,
        public int $levelStartXp,
        public int $nextLevelXp,
        public int $streak,
        public int $bestStreak,
        public CritterView $critter,
        public array $newAchievements,
    ) {
    }
}
