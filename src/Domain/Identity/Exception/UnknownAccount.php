<?php

declare(strict_types=1);

namespace App\Domain\Identity\Exception;

final class UnknownAccount extends InvalidUser
{
    public function __construct(string $email)
    {
        parent::__construct('user.unknown_account', ['email' => $email]);
    }
}
