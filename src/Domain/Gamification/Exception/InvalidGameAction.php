<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Exception;

use App\Domain\Shared\Exception\DomainException;

abstract class InvalidGameAction extends DomainException
{
}
