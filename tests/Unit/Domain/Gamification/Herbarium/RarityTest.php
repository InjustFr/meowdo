<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification\Herbarium;

use App\Domain\Gamification\Herbarium\Rarity;
use PHPUnit\Framework\TestCase;

final class RarityTest extends TestCase
{
    public function testRarerMossesYieldMoreDew(): void
    {
        self::assertSame(
            [2, 3, 5, 8],
            array_map(static fn (Rarity $rarity): int => $rarity->dewYield(), [Rarity::Common, Rarity::Uncommon, Rarity::Rare, Rarity::VeryRare]),
        );
    }
}
