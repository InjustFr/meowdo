<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification\Herbarium;

use App\Domain\Gamification\Herbarium\Species;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use App\Domain\Gamification\Herbarium\SpeciesDraw;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

final class SpeciesDrawTest extends TestCase
{
    public function testDrawsOneSpeciesPerLevelCrossed(): void
    {
        self::assertCount(1, $this->draw()->draw(1, []));
        self::assertCount(2, $this->draw()->draw(2, []));
    }

    public function testDrawsNothingWithoutALevelCrossed(): void
    {
        self::assertSame([], $this->draw()->draw(0, []));
    }

    public function testNeverDrawsASpeciesAlreadyCollected(): void
    {
        $catalog = SpeciesCatalog::all();
        $collected = \array_slice($catalog, 0, \count($catalog) - 1);

        self::assertEquals([array_last($catalog)], $this->draw()->draw(1, $collected));
    }

    public function testDrawsNothingOnceTheHerbariumIsComplete(): void
    {
        self::assertSame([], $this->draw()->draw(1, SpeciesCatalog::all()));
    }

    public function testDrawsDistinctSpecies(): void
    {
        $drawn = $this->draw()->draw(\count(SpeciesCatalog::all()), []);

        self::assertCount(\count(SpeciesCatalog::all()), array_unique(array_map(static fn (Species $species): string => $species->slug, $drawn)));
    }

    public function testTheDrawIsRandom(): void
    {
        $first = new SpeciesDraw(new Randomizer(new Mt19937(1)))->draw(5, []);
        $second = new SpeciesDraw(new Randomizer(new Mt19937(2)))->draw(5, []);

        self::assertNotEquals($first, $second);
    }

    private function draw(): SpeciesDraw
    {
        return new SpeciesDraw(new Randomizer(new Mt19937(42)));
    }
}
