<?php

declare(strict_types=1);

namespace App\Application\Gamification\ListHerbarium;

use App\Application\Gamification\SpecimenView;
use App\Application\Identity\CurrentUser;
use App\Domain\Gamification\Herbarium\Rarity;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use App\Domain\Gamification\Herbarium\Specimen;
use App\Domain\Gamification\Herbarium\SpecimenRepository;

final readonly class ListHerbariumHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private SpecimenRepository $specimens,
    ) {
    }

    public function __invoke(): HerbariumView
    {
        $specimens = $this->specimens->of($this->currentUser->get());
        $collected = array_map(static fn (Specimen $specimen): string => $specimen->species()->slug, $specimens);
        $remaining = array_fill_keys(array_map(static fn (Rarity $rarity): string => $rarity->value, Rarity::cases()), 0);
        foreach (SpeciesCatalog::all() as $species) {
            if (!\in_array($species->slug, $collected, true)) {
                ++$remaining[$species->rarity->value];
            }
        }

        return new HerbariumView(
            \count(SpeciesCatalog::all()),
            array_map(static fn (Specimen $specimen, int $index): SpecimenView => SpecimenView::of($specimen, $index + 1), $specimens, array_keys($specimens)),
            $remaining,
        );
    }
}
