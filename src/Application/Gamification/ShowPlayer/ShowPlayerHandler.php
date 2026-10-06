<?php

declare(strict_types=1);

namespace App\Application\Gamification\ShowPlayer;

use App\Application\Gamification\CatView;
use App\Application\Gamification\PlayerView;
use App\Application\Identity\CurrentUser;
use App\Application\Planning\Today;
use App\Domain\Gamification\Achievement\UnlockedAchievement;
use App\Domain\Gamification\Achievement\UnlockedAchievementRepository;
use App\Domain\Gamification\CatMood;
use App\Domain\Gamification\CatRepository;
use App\Domain\Gamification\LevelCurve;
use App\Domain\Gamification\PlayerRepository;

final readonly class ShowPlayerHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private PlayerRepository $players,
        private CatRepository $cats,
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
            CatView::of($this->cats->of($user), CatMood::of($player->streak(), $today)),
            array_values(array_map(static fn (UnlockedAchievement $achievement): string => $achievement->achievement(), $unseen)),
        );
    }
}
