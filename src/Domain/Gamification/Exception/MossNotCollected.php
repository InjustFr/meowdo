<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class MossNotCollected extends InvalidGameAction
{
    public function __construct(string $species)
    {
        parent::__construct('game.moss_not_collected', ['species' => $species]);
    }
}
