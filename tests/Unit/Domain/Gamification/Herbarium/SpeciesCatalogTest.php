<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification\Herbarium;

use App\Domain\Gamification\Exception\UnknownSpecies;
use App\Domain\Gamification\Herbarium\Rarity;
use App\Domain\Gamification\Herbarium\Species;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use App\Tests\Support\Json;
use PHPUnit\Framework\TestCase;

final class SpeciesCatalogTest extends TestCase
{
    private const string ROOT = __DIR__.'/../../../../..';

    public function testTheSlugComesFromTheScientificName(): void
    {
        self::assertSame('ptilium-crista-castrensis', new Species('Ptilium crista-castrensis', Rarity::Uncommon)->slug);
        self::assertSame('Polytrichum commune', SpeciesCatalog::get('polytrichum-commune')->scientificName);
    }

    public function testEveryRarityHasSpecies(): void
    {
        $counts = array_count_values(array_map(static fn (Species $species): string => $species->rarity->value, SpeciesCatalog::all()));

        self::assertSame(['common' => 16, 'rare' => 14, 'uncommon' => 13, 'very_rare' => 5], array_merge(array_fill_keys(['common', 'rare', 'uncommon', 'very_rare'], 0), $counts));
    }

    public function testRarerSpeciesWeighLess(): void
    {
        self::assertSame([8, 4, 2, 1], array_map(static fn (Rarity $rarity): int => $rarity->weight(), Rarity::cases()));
    }

    public function testUnknownSpecies(): void
    {
        $this->expectExceptionObject(new UnknownSpecies('dandelion'));

        SpeciesCatalog::get('dandelion');
    }

    public function testEverySpeciesHasAnOpenlyLicensedPhotoANameAndANote(): void
    {
        $slugs = array_map(static fn (Species $species): string => $species->slug, SpeciesCatalog::all());
        $imported = Json::decode((string) file_get_contents(self::ROOT.'/assets/vue/herbarium/species.json'));

        self::assertSame($slugs, array_keys($imported));
        foreach ($slugs as $slug) {
            self::assertContains(Json::string($imported, $slug, 'license'), ['cc0', 'cc-by']);
            self::assertFileExists(self::ROOT.'/public'.Json::string($imported, $slug, 'photo'));
        }
        foreach (['en', 'fr'] as $locale) {
            foreach (['species', 'speciesNotes'] as $namespace) {
                $texts = Json::decode((string) file_get_contents(self::ROOT.'/assets/vue/i18n/'.$locale.'/'.$namespace.'.json'));
                self::assertEqualsCanonicalizing($slugs, array_keys($texts), $locale.'/'.$namespace);
            }
        }
    }
}
