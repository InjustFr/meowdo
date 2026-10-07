<?php

declare(strict_types=1);

namespace App\Application\Planning\ShowStatistics;

final readonly class OnTimeView
{
    public function __construct(
        public int $onTime,
        public int $late,
    ) {
    }
}
