<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Domain\Gamification\Critter;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CritterNamePayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'name.required')]
        #[Assert\Length(max: Critter::MAX_NAME_LENGTH, maxMessage: 'name.too_long')]
        public string $name = '',
    ) {
    }
}
