<?php

declare(strict_types=1);

namespace App\Application\Gamification\UpgradeFacility;

use App\Application\AtomicChange;
use App\Application\Gamification\AchievementCheck;
use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\Greenhouse\Facility;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;

final readonly class UpgradeFacilityHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private GreenhouseRepository $greenhouses,
        private AtomicChange $atomicChange,
        private Transaction $transaction,
        private AchievementCheck $achievements,
    ) {
    }

    public function __invoke(Facility $facility): void
    {
        $this->atomicChange->apply(function () use ($facility): void {
            $this->greenhouses->lockedOf($this->currentUser->get())->upgrade($facility);
            $this->transaction->commit();
        });
        ($this->achievements)();
    }
}
