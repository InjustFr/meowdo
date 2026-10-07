<?php

declare(strict_types=1);

namespace App\Application\Planning\ShowStatistics;

final readonly class DayCountView
{
    public function __construct(
        public string $date,
        public int $count,
    ) {
    }
}
