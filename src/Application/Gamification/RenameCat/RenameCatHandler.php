<?php

declare(strict_types=1);

namespace App\Application\Gamification\RenameCat;

use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\CatRepository;

final readonly class RenameCatHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private CatRepository $cats,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(string $name): void
    {
        $this->cats->of($this->currentUser->get())->rename($name);
        $this->transaction->commit();
    }
}
