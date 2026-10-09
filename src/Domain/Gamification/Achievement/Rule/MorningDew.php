<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\PlayerStats;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 75)]
final readonly class MorningDew extends Threshold
{
    public function id(): string
    {
        return 'dew_1000';
    }

    protected function measure(PlayerStats $stats): int
    {
        return $stats->dewGathered;
    }

    protected function target(): int
    {
        return 1_000;
    }
}
