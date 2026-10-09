<?php

declare(strict_types=1);

namespace App\Application\Gamification\PlantMoss;

use App\Application\AtomicChange;
use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\Exception\MossNotCollected;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use App\Domain\Gamification\Herbarium\SpecimenRepository;
use Psr\Clock\ClockInterface;

final readonly class PlantMossHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private GreenhouseRepository $greenhouses,
        private SpecimenRepository $specimens,
        private AtomicChange $atomicChange,
        private Transaction $transaction,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(PlantMoss $command): void
    {
        $user = $this->currentUser->get();
        $specimen = $this->specimens->ofSpecies($user, SpeciesCatalog::get($command->species)) ?? throw new MossNotCollected($command->species);
        $this->atomicChange->apply(function () use ($user, $specimen, $command): void {
            $this->greenhouses->lockedOf($user)->plant($command->pot, $specimen, $this->clock->now());
            $this->transaction->commit();
        });
    }
}
