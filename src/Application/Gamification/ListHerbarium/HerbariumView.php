<?php

declare(strict_types=1);

namespace App\Application\Gamification\ListHerbarium;

use App\Application\Gamification\SpecimenView;

final readonly class HerbariumView
{
    /**
     * @param list<SpecimenView> $specimens
     */
    public function __construct(
        public int $total,
        public array $specimens,
    ) {
    }
}
