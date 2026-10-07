<?php

declare(strict_types=1);

namespace App\Application\Planning\ShowStatistics;

final readonly class StatisticsView
{
    /**
     * @param list<DayCountView>     $perDay
     * @param array<string, int>     $byQuadrant
     * @param list<ProjectCountView> $byProject
     */
    public function __construct(
        public string $today,
        public TotalsView $totals,
        public int $streak,
        public int $bestStreak,
        public array $perDay,
        public array $byQuadrant,
        public array $byProject,
        public OnTimeView $onTime,
    ) {
    }
}
