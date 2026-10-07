<?php

declare(strict_types=1);

namespace App\Application\Gamification\WearCosmetic;

use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\Cosmetic\CosmeticCatalog;
use App\Domain\Gamification\Cosmetic\OwnershipRepository;
use App\Domain\Gamification\CritterRepository;
use App\Domain\Gamification\Exception\CosmeticNotOwned;

final readonly class WearCosmeticHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private CritterRepository $critters,
        private OwnershipRepository $ownerships,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(string $slug): void
    {
        $user = $this->currentUser->get();
        CosmeticCatalog::get($slug);
        $ownership = $this->ownerships->find($user, $slug) ?? throw new CosmeticNotOwned($slug);
        $this->critters->of($user)->wear($ownership);
        $this->transaction->commit();
    }
}
