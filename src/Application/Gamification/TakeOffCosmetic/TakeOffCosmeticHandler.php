<?php

declare(strict_types=1);

namespace App\Application\Gamification\TakeOffCosmetic;

use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\CatRepository;
use App\Domain\Gamification\Cosmetic\Slot;

final readonly class TakeOffCosmeticHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private CatRepository $cats,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(Slot $slot): void
    {
        $this->cats->of($this->currentUser->get())->takeOff($slot);
        $this->transaction->commit();
    }
}
