<?php

declare(strict_types=1);

namespace App\Application\Gamification\LaunchExpedition;

use App\Application\Gamification\AchievementCheck;
use App\Application\Gamification\CollectSpecies;
use App\Application\Gamification\SpeciesView;
use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\Exception\HerbariumComplete;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use App\Domain\Gamification\Herbarium\SpecimenRepository;

final readonly class LaunchExpeditionHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private GreenhouseRepository $greenhouses,
        private SpecimenRepository $specimens,
        private CollectSpecies $collectSpecies,
        private Transaction $transaction,
        private AchievementCheck $achievements,
    ) {
    }

    public function __invoke(): ExpeditionView
    {
        $user = $this->currentUser->get();
        if (\count($this->specimens->of($user)) >= \count(SpeciesCatalog::all())) {
            throw new HerbariumComplete();
        }
        $greenhouse = $this->greenhouses->of($user);
        $greenhouse->fundExpedition();
        $found = ($this->collectSpecies)($user, 1)[0] ?? throw new HerbariumComplete();
        $this->transaction->commit();
        ($this->achievements)();

        return new ExpeditionView(SpeciesView::of($found), $greenhouse->expeditionCost());
    }
}
