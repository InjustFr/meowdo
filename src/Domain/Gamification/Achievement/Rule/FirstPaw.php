<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\PlayerStats;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 130)]
final readonly class FirstPaw extends Threshold
{
    public function id(): string
    {
        return 'first_paw';
    }

    protected function measure(PlayerStats $stats): int
    {
        return $stats->tasksCompleted;
    }

    protected function target(): int
    {
        return 1;
    }
}
