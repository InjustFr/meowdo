<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\PlayerStats;

final readonly class MatrixSorter extends Threshold
{
    public function id(): string
    {
        return 'matrix_sorter';
    }

    protected function measure(PlayerStats $stats): int
    {
        return $stats->tasksClassified;
    }

    protected function target(): int
    {
        return 20;
    }
}
