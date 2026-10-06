<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Domain\Planning\Project;
use App\Domain\Planning\ProjectColor;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ProjectPayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'name.required')]
        #[Assert\Length(max: Project::MAX_NAME_LENGTH, maxMessage: 'name.too_long')]
        public string $name = '',
        public ProjectColor $color = ProjectColor::Lamp,
    ) {
    }
}
