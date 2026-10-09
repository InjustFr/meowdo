<?php

declare(strict_types=1);

namespace App\Application\Gamification\ShowPlayer;

use App\Application\Gamification\PlayerView;
use App\Application\Identity\CurrentUser;
use App\Application\Planning\Today;
use App\Domain\Gamification\Achievement\UnlockedAchievement;
use App\Domain\Gamification\Achievement\UnlockedAchievementRepository;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use App\Domain\Gamification\Herbarium\SpecimenRepository;
use App\Domain\Gamification\LevelCurve;
use App\Domain\Gamification\PlayerRepository;

final readonly class ShowPlayerHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private PlayerRepository $players,
        private SpecimenRepository $specimens,
        private UnlockedAchievementRepository $achievements,
        private GreenhouseRepository $greenhouses,
        private Today $today,
    ) {
    }

    public function __invoke(): PlayerView
    {
        $user = $this->currentUser->get();
        $player = $this->players->of($user);
        $level = $player->level();
        $greenhouse = $this->greenhouses->of($user);
        $unseen = array_filter($this->achievements->of($user), static fn (UnlockedAchievement $achievement): bool => !$achievement->isSeen());

        return new PlayerView(
            $user->displayName(),
            $player->xp(),
            $level,
            LevelCurve::thresholdOf($level),
            LevelCurve::thresholdOf($level + 1),
            $player->streak()->asOf($this->today->date()),
            $player->streak()->best,
            \count($this->specimens->of($user)),
            \count(SpeciesCatalog::all()),
            $greenhouse->dew(),
            $greenhouse->isFullAt($this->today->now()),
            array_values(array_map(static fn (UnlockedAchievement $achievement): string => $achievement->achievement(), $unseen)),
        );
    }
}
