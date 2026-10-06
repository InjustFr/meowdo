<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\PlayerStats;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 70)]
final readonly class PatientHunter extends Threshold
{
    public function id(): string
    {
        return 'stalk_25';
    }

    protected function measure(PlayerStats $stats): int
    {
        return $stats->scheduleCompleted;
    }

    protected function target(): int
    {
        return 25;
    }
}
