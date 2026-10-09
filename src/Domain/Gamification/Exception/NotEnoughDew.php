<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class NotEnoughDew extends InvalidGameAction
{
    public function __construct(int $cost, int $dew)
    {
        parent::__construct('game.not_enough_dew', ['cost' => $cost, 'dew' => $dew]);
    }
}
