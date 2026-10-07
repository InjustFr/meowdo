<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

enum Tint: string
{
    case Sprout = 'sprout';
    case Lichen = 'lichen';
    case Peat = 'peat';
    case Rust = 'rust';
    case Frost = 'frost';
    case Plum = 'plum';
}
