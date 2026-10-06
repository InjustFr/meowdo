<?php

declare(strict_types=1);

namespace App\Presentation\Api\Identity;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class TimezonePayload
{
    public function __construct(
        #[Assert\Timezone(message: 'timezone.invalid')]
        public string $timezone = 'UTC',
    ) {
    }
}
