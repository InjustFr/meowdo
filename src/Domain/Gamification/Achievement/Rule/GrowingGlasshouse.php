<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\PlayerStats;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 65)]
final readonly class GrowingGlasshouse extends Threshold
{
    public function id(): string
    {
        return 'glasshouse_5';
    }

    protected function measure(PlayerStats $stats): int
    {
        return $stats->glasshouseLevel;
    }

    protected function target(): int
    {
        return 5;
    }
}
