<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Domain\Gamification\Cat;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CatNamePayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'name.required')]
        #[Assert\Length(max: Cat::MAX_NAME_LENGTH, maxMessage: 'name.too_long')]
        public string $name = '',
    ) {
    }
}
