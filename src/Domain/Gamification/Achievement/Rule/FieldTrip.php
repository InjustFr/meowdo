<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\PlayerStats;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 90)]
final readonly class FieldTrip extends Threshold
{
    public function id(): string
    {
        return 'field_trip';
    }

    protected function measure(PlayerStats $stats): int
    {
        return $stats->expeditions;
    }

    protected function target(): int
    {
        return 1;
    }
}
