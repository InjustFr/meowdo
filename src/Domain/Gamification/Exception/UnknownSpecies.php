<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

final class UnknownSpecies extends InvalidGameAction
{
    public function __construct(string $slug)
    {
        parent::__construct('game.unknown_species', ['species' => $slug]);
    }
}
