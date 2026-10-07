<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Herbarium;

use Random\Randomizer;

final readonly class SpeciesDraw
{
    public function __construct(private Randomizer $randomizer)
    {
    }

    /**
     * @param list<Species> $collected
     *
     * @return list<Species>
     */
    public function draw(int $count, array $collected): array
    {
        if ($count <= 0) {
            return [];
        }

        $slugs = array_map(static fn (Species $species): string => $species->slug, $collected);
        $remaining = array_values(array_filter(
            SpeciesCatalog::all(),
            static fn (Species $species): bool => !\in_array($species->slug, $slugs, true),
        ));

        return \array_slice($this->randomizer->shuffleArray($remaining), 0, $count);
    }
}
