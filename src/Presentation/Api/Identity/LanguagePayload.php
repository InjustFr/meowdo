<?php

declare(strict_types=1);

namespace App\Presentation\Api\Identity;

use App\Domain\Identity\Language;

final readonly class LanguagePayload
{
    public function __construct(
        public Language $language = Language::English,
    ) {
    }
}
