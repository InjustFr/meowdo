<?php

declare(strict_types=1);

namespace App\Application\Planning\ShowStatistics;

interface StatisticsQueries
{
    public function countCompleted(?\DateTimeImmutable $since = null): int;

    public function countOpen(): int;

    public function countOverdue(\DateTimeImmutable $today): int;
}
