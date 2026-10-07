<?php

declare(strict_types=1);

namespace App\Application\Identity\CreateUser;

use App\Domain\Gamification\Tint;

final readonly class CreateUser
{
    public function __construct(
        public string $email,
        public string $displayName,
        public string $timezone,
        public string $critterName,
        public Tint $tint,
    ) {
    }
}
