<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Herbarium;

use App\Domain\Gamification\Exception\UnknownSpecies;

final class SpeciesCatalog
{
    private const array SCIENTIFIC_NAMES = [
        'Polytrichum commune',
        'Polytrichum juniperinum',
        'Sphagnum palustre',
        'Sphagnum capillifolium',
        'Hylocomium splendens',
        'Pleurozium schreberi',
        'Hypnum cupressiforme',
        'Dicranum scoparium',
        'Leucobryum glaucum',
        'Thuidium tamariscinum',
        'Ceratodon purpureus',
        'Funaria hygrometrica',
        'Bryum argenteum',
        'Tortula muralis',
        'Grimmia pulvinata',
        'Syntrichia ruralis',
        'Kindbergia praelonga',
        'Brachythecium rutabulum',
        'Rhytidiadelphus squarrosus',
        'Rhytidiadelphus loreus',
        'Atrichum undulatum',
        'Mnium hornum',
        'Plagiomnium undulatum',
        'Plagiomnium affine',
        'Fissidens taxifolius',
        'Climacium dendroides',
        'Fontinalis antipyretica',
        'Homalothecium sericeum',
        'Pseudoscleropodium purum',
        'Leucodon sciuroides',
        'Ulota crispa',
        'Tetraphis pellucida',
        'Buxbaumia aphylla',
        'Splachnum luteum',
        'Schistostega pennata',
        'Hookeria lucens',
        'Calliergonella cuspidata',
        'Racomitrium lanuginosum',
        'Physcomitrium pyriforme',
        'Aulacomnium palustre',
        'Dicranella heteromalla',
        'Pogonatum urnigerum',
        'Ptilium crista-castrensis',
        'Andreaea rupestris',
        'Rhizomnium punctatum',
        'Bartramia pomiformis',
        'Encalypta streptocarpa',
        'Hedwigia ciliata',
    ];

    /**
     * @return list<Species>
     */
    public static function all(): array
    {
        return array_map(static fn (string $name): Species => new Species($name), self::SCIENTIFIC_NAMES);
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
