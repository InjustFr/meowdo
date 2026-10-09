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

    public function dewYield(): int
    {
        return match ($this) {
            self::Common => 2,
            self::Uncommon => 3,
            self::Rare => 5,
            self::VeryRare => 8,
        };
    }
}
