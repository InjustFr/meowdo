<?php

declare(strict_types=1);

namespace App\Application\Gamification\UpgradeFacility;

use App\Application\Gamification\AchievementCheck;
use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\Greenhouse\Facility;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;
use Psr\Clock\ClockInterface;

final readonly class UpgradeFacilityHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private GreenhouseRepository $greenhouses,
        private Transaction $transaction,
        private AchievementCheck $achievements,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(Facility $facility): void
    {
        $this->greenhouses->of($this->currentUser->get())->upgrade($facility, $this->clock->now());
        $this->transaction->commit();
        ($this->achievements)();
    }
}
