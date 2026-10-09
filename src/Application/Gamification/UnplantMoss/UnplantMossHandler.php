<?php

declare(strict_types=1);

namespace App\Application\Gamification\UnplantMoss;

use App\Application\AtomicChange;
use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;

final readonly class UnplantMossHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private GreenhouseRepository $greenhouses,
        private AtomicChange $atomicChange,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(int $pot): void
    {
        $this->atomicChange->apply(function () use ($pot): void {
            $this->greenhouses->lockedOf($this->currentUser->get())->unplant($pot);
            $this->transaction->commit();
        });
    }
}
