<?php

declare(strict_types=1);

namespace App\Application\Gamification\ListHerbarium;

use App\Application\Gamification\SpecimenView;
use App\Application\Identity\CurrentUser;
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
        return new HerbariumView(
            \count(SpeciesCatalog::all()),
            array_map(static fn (Specimen $specimen): SpecimenView => SpecimenView::of($specimen), $this->specimens->of($this->currentUser->get())),
        );
    }
}
