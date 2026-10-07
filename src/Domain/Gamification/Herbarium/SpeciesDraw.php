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
        $slugs = array_map(static fn (Species $species): string => $species->slug, $collected);
        $remaining = array_values(array_filter(
            SpeciesCatalog::all(),
            static fn (Species $species): bool => !\in_array($species->slug, $slugs, true),
        ));

        $drawn = [];
        while (\count($drawn) < $count && [] !== $remaining) {
            $index = $this->pick($remaining);
            $drawn[] = $remaining[$index];
            array_splice($remaining, $index, 1);
        }

        return $drawn;
    }

    /**
     * @param non-empty-list<Species> $species
     */
    private function pick(array $species): int
    {
        $roll = $this->randomizer->getInt(1, array_sum(array_map(static fn (Species $one): int => $one->rarity->weight(), $species)));
        foreach ($species as $index => $one) {
            $roll -= $one->rarity->weight();
            if ($roll <= 0) {
                return $index;
            }
        }

        return array_key_last($species);
    }
}
