<?php

declare(strict_types=1);

namespace App\Application\Planning\ShowStatistics;

final readonly class TotalsView
{
    public function __construct(
        public int $completed,
        public int $completedThisWeek,
        public int $completedThisMonth,
        public int $open,
        public int $overdue,
    ) {
    }
}
