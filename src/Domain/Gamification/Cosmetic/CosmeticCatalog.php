<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Cosmetic;

use App\Domain\Gamification\Exception\UnknownCosmetic;

final class CosmeticCatalog
{
    /**
     * @return list<CosmeticItem>
     */
    public static function all(): array
    {
        return [
            new CosmeticItem('party-hat', Slot::Hat, 30),
            new CosmeticItem('beanie', Slot::Hat, 40),
            new CosmeticItem('frog-hat', Slot::Hat, 60, 3),
            new CosmeticItem('wizard-hat', Slot::Hat, 90, 4),
            new CosmeticItem('crown', Slot::Hat, 200, 8),
            new CosmeticItem('bell-collar', Slot::Neckwear, 20),
            new CosmeticItem('bow-tie', Slot::Neckwear, 35),
            new CosmeticItem('bandana', Slot::Neckwear, 45, 2),
            new CosmeticItem('scarf', Slot::Neckwear, 55, 3),
            new CosmeticItem('yarn-ball', Slot::Toy, 15),
            new CosmeticItem('fish', Slot::Toy, 25),
            new CosmeticItem('mouse', Slot::Toy, 35, 2),
            new CosmeticItem('laser-dot', Slot::Toy, 60, 4),
            new CosmeticItem('rooftop', Slot::Backdrop, 50, 2),
            new CosmeticItem('library', Slot::Backdrop, 90, 4),
            new CosmeticItem('aquarium', Slot::Backdrop, 120, 5),
            new CosmeticItem('sakura', Slot::Backdrop, 160, 7),
        ];
    }

    public static function get(string $slug): CosmeticItem
    {
        foreach (self::all() as $item) {
            if ($item->slug === $slug) {
                return $item;
            }
        }

        throw new UnknownCosmetic($slug);
    }
}
