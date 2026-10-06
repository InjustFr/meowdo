<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class CosmeticNotOwned extends InvalidGameAction
{
    public function __construct(string $slug)
    {
        parent::__construct('game.not_owned', ['item' => $slug]);
    }
}
