<?php

declare(strict_types=1);

namespace App\Application\Gamification\TakeOffCosmetic;

use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\Cosmetic\Slot;
use App\Domain\Gamification\CritterRepository;

final readonly class TakeOffCosmeticHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private CritterRepository $critters,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(Slot $slot): void
    {
        $this->critters->of($this->currentUser->get())->takeOff($slot);
        $this->transaction->commit();
    }
}
