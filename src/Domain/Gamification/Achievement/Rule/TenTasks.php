<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\PlayerStats;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 120)]
final readonly class TenTasks extends Threshold
{
    public function id(): string
    {
        return 'ten_tasks';
    }

    protected function measure(PlayerStats $stats): int
    {
        return $stats->tasksCompleted;
    }

    protected function target(): int
    {
        return 10;
    }
}
