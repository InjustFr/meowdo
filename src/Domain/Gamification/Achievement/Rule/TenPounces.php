<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\PlayerStats;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 80)]
final readonly class TenPounces extends Threshold
{
    public function id(): string
    {
        return 'pounce_10';
    }

    protected function measure(PlayerStats $stats): int
    {
        return $stats->doFirstCompleted;
    }

    protected function target(): int
    {
        return 10;
    }
}
