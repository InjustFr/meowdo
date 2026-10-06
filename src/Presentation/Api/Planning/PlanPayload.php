<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Domain\Planning\PlanShortcut;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class PlanPayload
{
    public function __construct(
        public PlanShortcut $when = PlanShortcut::Today,
        #[Assert\Date(message: 'date.invalid')]
        public ?string $date = null,
    ) {
    }
}
