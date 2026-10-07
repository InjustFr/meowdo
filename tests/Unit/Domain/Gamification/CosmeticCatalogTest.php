<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification;

use App\Domain\Gamification\Cosmetic\CosmeticCatalog;
use App\Domain\Gamification\Cosmetic\CosmeticItem;
use App\Domain\Gamification\Cosmetic\Slot;
use App\Domain\Gamification\Exception\UnknownCosmetic;
use PHPUnit\Framework\TestCase;

final class CosmeticCatalogTest extends TestCase
{
    public function testGetsAnItemBySlug(): void
    {
        self::assertEquals(new CosmeticItem('toadstool', Slot::Hat, 60, 3), CosmeticCatalog::get('toadstool'));
    }

    public function testUnknownSlugThrows(): void
    {
        $this->expectExceptionObject(new UnknownCosmetic('top-hat'));

        CosmeticCatalog::get('top-hat');
    }

    public function testSlugsAreUniqueAndEverySlotIsStocked(): void
    {
        $items = CosmeticCatalog::all();
        $slugs = array_map(static fn (CosmeticItem $item): string => $item->slug, $items);

        self::assertSame($slugs, array_values(array_unique($slugs)));
        foreach (Slot::cases() as $slot) {
            self::assertNotEmpty(array_filter($items, static fn (CosmeticItem $item): bool => $slot === $item->slot), $slot->value);
        }
        foreach ($items as $item) {
            self::assertGreaterThan(0, $item->price);
            self::assertLessThanOrEqual(40, \strlen($item->slug));
        }
    }
}
