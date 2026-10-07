<?php

declare(strict_types=1);

namespace App\Application\Gamification\ListShop;

use App\Application\Identity\CurrentUser;
use App\Domain\Gamification\Cosmetic\CosmeticCatalog;
use App\Domain\Gamification\Cosmetic\CosmeticItem;
use App\Domain\Gamification\Cosmetic\OwnershipRepository;
use App\Domain\Gamification\CritterRepository;

final readonly class ListShopHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private OwnershipRepository $ownerships,
        private CritterRepository $critters,
    ) {
    }

    /**
     * @return list<ShopItemView>
     */
    public function __invoke(): array
    {
        $user = $this->currentUser->get();
        $owned = $this->ownerships->slugsOf($user);
        $worn = array_values(array_filter($this->critters->of($user)->outfit()));

        return array_map(
            static fn (CosmeticItem $item): ShopItemView => ShopItemView::of($item, \in_array($item->slug, $owned, true), \in_array($item->slug, $worn, true)),
            CosmeticCatalog::all(),
        );
    }
}
