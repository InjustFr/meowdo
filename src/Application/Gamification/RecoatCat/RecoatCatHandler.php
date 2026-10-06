<?php

declare(strict_types=1);

namespace App\Application\Gamification\RecoatCat;

use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\CatRepository;
use App\Domain\Gamification\Coat;

final readonly class RecoatCatHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private CatRepository $cats,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(Coat $coat): void
    {
        $this->cats->of($this->currentUser->get())->recoat($coat);
        $this->transaction->commit();
    }
}
