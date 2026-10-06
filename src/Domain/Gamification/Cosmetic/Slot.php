<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Cosmetic;

enum Slot: string
{
    case Hat = 'hat';
    case Neckwear = 'neckwear';
    case Toy = 'toy';
    case Backdrop = 'backdrop';
}
