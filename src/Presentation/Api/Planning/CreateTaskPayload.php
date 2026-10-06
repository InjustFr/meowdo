<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Domain\Planning\PlanShortcut;
use App\Domain\Planning\Quadrant;
use App\Domain\Planning\Task;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateTaskPayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'title.required')]
        #[Assert\Length(max: Task::MAX_TITLE_LENGTH, maxMessage: 'title.too_long')]
        public string $title = '',
        public ?string $notes = null,
        public ?Ulid $projectId = null,
        public PlanShortcut $plan = PlanShortcut::None,
        #[Assert\Date(message: 'date.invalid')]
        public ?string $planDate = null,
        #[Assert\Date(message: 'date.invalid')]
        public ?string $dueOn = null,
        public ?Quadrant $quadrant = null,
    ) {
    }
}
