<?php

declare(strict_types=1);

namespace App\Application\Gamification;

use App\Domain\Gamification\Achievement\PlayerStats;
use App\Domain\Gamification\Player;

interface PlayerStatsLedger
{
    public function statsOf(Player $player): PlayerStats;
}
