<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement\Rule;

use App\Domain\Gamification\Achievement\PlayerStats;

final readonly class FirstPurchase extends Threshold
{
    public function id(): string
    {
        return 'first_purchase';
    }

    protected function measure(PlayerStats $stats): int
    {
        return $stats->purchases;
    }

    protected function target(): int
    {
        return 1;
    }
}
