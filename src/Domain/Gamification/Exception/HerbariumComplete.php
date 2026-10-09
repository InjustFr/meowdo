<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class HerbariumComplete extends InvalidGameAction
{
    public function __construct()
    {
        parent::__construct('game.herbarium_complete');
    }
}
