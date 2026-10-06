<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class CosmeticAlreadyOwned extends InvalidGameAction
{
    public function __construct(string $slug)
    {
        parent::__construct('game.already_owned', ['item' => $slug]);
    }
}
