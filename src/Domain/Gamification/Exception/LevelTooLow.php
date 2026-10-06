<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class LevelTooLow extends InvalidGameAction
{
    public function __construct(string $slug, int $level)
    {
        parent::__construct('game.level_too_low', ['item' => $slug, 'level' => $level]);
    }
}
