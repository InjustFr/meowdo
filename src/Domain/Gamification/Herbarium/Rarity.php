<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Herbarium;

enum Rarity: string
{
    case Common = 'common';
    case Uncommon = 'uncommon';
    case Rare = 'rare';
    case VeryRare = 'very_rare';

    public function weight(): int
    {
        return match ($this) {
            self::Common => 8,
            self::Uncommon => 4,
            self::Rare => 2,
            self::VeryRare => 1,
        };
    }
}
