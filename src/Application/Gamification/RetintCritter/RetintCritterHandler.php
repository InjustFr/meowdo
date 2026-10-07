<?php

declare(strict_types=1);

namespace App\Application\Gamification\RetintCritter;

use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\CritterRepository;
use App\Domain\Gamification\Tint;

final readonly class RetintCritterHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private CritterRepository $critters,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(Tint $tint): void
    {
        $this->critters->of($this->currentUser->get())->retint($tint);
        $this->transaction->commit();
    }
}
