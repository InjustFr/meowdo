<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class UnknownPot extends InvalidGameAction
{
    public function __construct(int $pot)
    {
        parent::__construct('game.unknown_pot', ['pot' => $pot]);
    }
}
