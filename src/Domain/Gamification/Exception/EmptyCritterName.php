<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class EmptyCritterName extends InvalidGameAction
{
    public function __construct()
    {
        parent::__construct('game.empty_critter_name');
    }
}
