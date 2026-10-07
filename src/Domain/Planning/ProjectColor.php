<?php

declare(strict_types=1);

namespace App\Domain\Planning;

enum ProjectColor: string
{
    case Berry = 'berry';
    case Honey = 'honey';
    case Moss = 'moss';
    case Fjord = 'fjord';
    case Heather = 'heather';
    case Blossom = 'blossom';
    case Lichen = 'lichen';
    case Rust = 'rust';
}
