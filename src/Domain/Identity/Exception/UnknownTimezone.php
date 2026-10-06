<?php

declare(strict_types=1);

namespace App\Domain\Identity\Exception;

final class UnknownTimezone extends InvalidUser
{
    public function __construct(string $timezone)
    {
        parent::__construct('user.unknown_timezone', ['timezone' => $timezone]);
    }
}
