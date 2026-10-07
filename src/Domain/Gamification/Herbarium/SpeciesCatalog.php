<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Herbarium;

use App\Domain\Gamification\Exception\UnknownSpecies;

final class SpeciesCatalog
{
    private const array SPECIES = [
        'Polytrichum commune' => Rarity::Common,
        'Polytrichum juniperinum' => Rarity::Common,
        'Sphagnum palustre' => Rarity::Rare,
        'Sphagnum capillifolium' => Rarity::Rare,
        'Hylocomium splendens' => Rarity::Common,
        'Pleurozium schreberi' => Rarity::Common,
        'Hypnum cupressiforme' => Rarity::Common,
        'Dicranum scoparium' => Rarity::Common,
        'Leucobryum glaucum' => Rarity::Common,
        'Thuidium tamariscinum' => Rarity::Uncommon,
        'Ceratodon purpureus' => Rarity::Common,
        'Funaria hygrometrica' => Rarity::Common,
        'Bryum argenteum' => Rarity::Common,
        'Tortula muralis' => Rarity::Common,
        'Grimmia pulvinata' => Rarity::Common,
        'Syntrichia ruralis' => Rarity::Common,
        'Kindbergia praelonga' => Rarity::Uncommon,
        'Brachythecium rutabulum' => Rarity::Common,
        'Rhytidiadelphus squarrosus' => Rarity::Uncommon,
        'Rhytidiadelphus loreus' => Rarity::Uncommon,
        'Atrichum undulatum' => Rarity::Common,
        'Mnium hornum' => Rarity::Uncommon,
        'Plagiomnium undulatum' => Rarity::Uncommon,
        'Plagiomnium affine' => Rarity::Rare,
        'Fissidens taxifolius' => Rarity::Rare,
        'Climacium dendroides' => Rarity::Uncommon,
        'Fontinalis antipyretica' => Rarity::Rare,
        'Homalothecium sericeum' => Rarity::Uncommon,
        'Pseudoscleropodium purum' => Rarity::Uncommon,
        'Leucodon sciuroides' => Rarity::Rare,
        'Ulota crispa' => Rarity::Uncommon,
        'Tetraphis pellucida' => Rarity::Rare,
        'Buxbaumia aphylla' => Rarity::VeryRare,
        'Splachnum luteum' => Rarity::VeryRare,
        'Schistostega pennata' => Rarity::VeryRare,
        'Hookeria lucens' => Rarity::VeryRare,
        'Calliergonella cuspidata' => Rarity::Rare,
        'Racomitrium lanuginosum' => Rarity::Uncommon,
        'Physcomitrium pyriforme' => Rarity::Rare,
        'Aulacomnium palustre' => Rarity::Uncommon,
        'Dicranella heteromalla' => Rarity::Rare,
        'Pogonatum urnigerum' => Rarity::Rare,
        'Ptilium crista-castrensis' => Rarity::Uncommon,
        'Andreaea rupestris' => Rarity::VeryRare,
        'Rhizomnium punctatum' => Rarity::Rare,
        'Bartramia pomiformis' => Rarity::Rare,
        'Encalypta streptocarpa' => Rarity::Rare,
        'Hedwigia ciliata' => Rarity::Common,
    ];

    /**
     * @return list<Species>
     */
    public static function all(): array
    {
        return array_map(static fn (string $name, Rarity $rarity): Species => new Species($name, $rarity), array_keys(self::SPECIES), self::SPECIES);
    }

    public static function get(string $slug): Species
    {
        foreach (self::all() as $species) {
            if ($species->slug === $slug) {
                return $species;
            }
        }

        throw new UnknownSpecies($slug);
    }
}
