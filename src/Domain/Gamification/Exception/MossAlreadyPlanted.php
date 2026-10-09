<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class MossAlreadyPlanted extends InvalidGameAction
{
    public function __construct(int $pot)
    {
        parent::__construct('game.moss_already_planted', ['pot' => $pot]);
    }
}
