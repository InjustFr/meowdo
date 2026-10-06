<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\PlayerStats;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 110)]
final readonly class ThreeDayStreak extends Threshold
{
    public function id(): string
    {
        return 'streak_3';
    }

    protected function measure(PlayerStats $stats): int
    {
        return $stats->bestStreak;
    }

    protected function target(): int
    {
        return 3;
    }
}
