<?php

declare(strict_types=1);

namespace App\Application\Gamification\ShowPlayer;

use App\Application\Gamification\CritterView;
use App\Application\Gamification\PlayerView;
use App\Application\Identity\CurrentUser;
use App\Application\Planning\Today;
use App\Domain\Gamification\Achievement\UnlockedAchievement;
use App\Domain\Gamification\Achievement\UnlockedAchievementRepository;
use App\Domain\Gamification\CritterMood;
use App\Domain\Gamification\CritterRepository;
use App\Domain\Gamification\LevelCurve;
use App\Domain\Gamification\PlayerRepository;

final readonly class ShowPlayerHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private PlayerRepository $players,
        private CritterRepository $critters,
        private UnlockedAchievementRepository $achievements,
        private Today $today,
    ) {
    }

    public function __invoke(): PlayerView
    {
        $user = $this->currentUser->get();
        $player = $this->players->of($user);
        $today = $this->today->date();
        $level = $player->level();
        $unseen = array_filter($this->achievements->of($user), static fn (UnlockedAchievement $achievement): bool => !$achievement->isSeen());

        return new PlayerView(
            $user->displayName(),
            $player->xp(),
            $player->coins(),
            $level,
            LevelCurve::thresholdOf($level),
            LevelCurve::thresholdOf($level + 1),
            $player->streak()->asOf($today),
            $player->streak()->best,
            CritterView::of($this->critters->of($user), CritterMood::of($player->streak(), $today)),
            array_values(array_map(static fn (UnlockedAchievement $achievement): string => $achievement->achievement(), $unseen)),
        );
    }
}
