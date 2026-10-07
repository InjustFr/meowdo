<?php

declare(strict_types=1);

namespace App\Application\Planning\ListDoneTasks;

final readonly class DoneView
{
    /**
     * @param list<DoneDayView> $days
     */
    public function __construct(
        public array $days,
        public ?string $older,
    ) {
    }
}
