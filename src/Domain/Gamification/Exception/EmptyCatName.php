<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class EmptyCatName extends InvalidGameAction
{
    public function __construct()
    {
        parent::__construct('game.empty_cat_name');
    }
}
