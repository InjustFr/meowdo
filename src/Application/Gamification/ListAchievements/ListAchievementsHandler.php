<?php

declare(strict_types=1);

namespace App\Application\Gamification\ListAchievements;

use App\Application\Identity\CurrentUser;
use App\Domain\Gamification\Achievement\AchievementReferee;
use App\Domain\Gamification\Achievement\AchievementRule;
use App\Domain\Gamification\Achievement\UnlockedAchievementRepository;

final readonly class ListAchievementsHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private AchievementReferee $referee,
        private UnlockedAchievementRepository $achievements,
    ) {
    }

    /**
     * @return list<AchievementView>
     */
    public function __invoke(): array
    {
        $unlockedAt = [];
        foreach ($this->achievements->of($this->currentUser->get()) as $achievement) {
            $unlockedAt[$achievement->achievement()] = $achievement->unlockedAt()->format(\DATE_ATOM);
        }

        return array_map(
            static fn (AchievementRule $rule): AchievementView => new AchievementView($rule->id(), $unlockedAt[$rule->id()] ?? null),
            $this->referee->rules(),
        );
    }
}
