<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class NotEnoughCoins extends InvalidGameAction
{
    public function __construct(string $slug, int $price, int $coins)
    {
        parent::__construct('game.not_enough_coins', ['item' => $slug, 'price' => $price, 'coins' => $coins]);
    }
}
