<?php

declare(strict_types=1);

namespace App\Domain\Identity\Exception;

final class AccountAlreadyActive extends InvalidUser
{
    public function __construct(string $email)
    {
        parent::__construct('user.already_active', ['email' => $email]);
    }
}
