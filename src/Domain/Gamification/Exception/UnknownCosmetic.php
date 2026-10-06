<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class UnknownCosmetic extends InvalidGameAction
{
    public function __construct(string $slug)
    {
        parent::__construct('game.unknown_cosmetic', ['item' => $slug]);
    }
}
