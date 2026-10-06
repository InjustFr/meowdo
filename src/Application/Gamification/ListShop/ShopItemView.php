<?php

declare(strict_types=1);

namespace App\Application\Gamification\ListShop;

use App\Domain\Gamification\Cosmetic\CosmeticItem;

final readonly class ShopItemView
{
    private function __construct(
        public string $slug,
        public string $slot,
        public int $price,
        public int $minLevel,
        public bool $owned,
        public bool $worn,
    ) {
    }

    public static function of(CosmeticItem $item, bool $owned, bool $worn): self
    {
        return new self($item->slug, $item->slot->value, $item->price, $item->minLevel, $owned, $worn);
    }
}
