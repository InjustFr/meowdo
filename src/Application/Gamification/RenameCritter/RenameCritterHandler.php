<?php

declare(strict_types=1);

namespace App\Application\Gamification\RenameCritter;

use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\CritterRepository;

final readonly class RenameCritterHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private CritterRepository $critters,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(string $name): void
    {
        $this->critters->of($this->currentUser->get())->rename($name);
        $this->transaction->commit();
    }
}
