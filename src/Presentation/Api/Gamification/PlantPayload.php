<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PlantPayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'greenhouse.species.blank')]
        public string $species = '',
    ) {
    }
}
