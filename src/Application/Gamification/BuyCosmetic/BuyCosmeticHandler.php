<?php

declare(strict_types=1);

namespace App\Application\Gamification\BuyCosmetic;

use App\Application\Gamification\AchievementCheck;
use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\Cosmetic\CosmeticCatalog;
use App\Domain\Gamification\Cosmetic\OwnershipRepository;
use App\Domain\Gamification\CritterRepository;
use App\Domain\Gamification\Exception\CosmeticAlreadyOwned;
use App\Domain\Gamification\PlayerRepository;
use Psr\Clock\ClockInterface;

final readonly class BuyCosmeticHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private PlayerRepository $players,
        private CritterRepository $critters,
        private OwnershipRepository $ownerships,
        private Transaction $transaction,
        private ClockInterface $clock,
        private AchievementCheck $achievements,
    ) {
    }

    public function __invoke(string $slug): void
    {
        $user = $this->currentUser->get();
        $item = CosmeticCatalog::get($slug);
        if (null !== $this->ownerships->find($user, $slug)) {
            throw new CosmeticAlreadyOwned($slug);
        }

        $ownership = $this->players->of($user)->buy($item, $this->clock->now());
        $this->ownerships->add($ownership);
        $this->critters->of($user)->wear($ownership);
        $this->transaction->commit();
        ($this->achievements)();
    }
}
