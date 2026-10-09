<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class PotIsEmpty extends InvalidGameAction
{
    public function __construct(int $pot)
    {
        parent::__construct('game.pot_is_empty', ['pot' => $pot]);
    }
}
