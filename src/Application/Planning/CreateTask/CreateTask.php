<?php

declare(strict_types=1);

namespace App\Application\Planning\CreateTask;

use App\Domain\Planning\PlanShortcut;
use App\Domain\Planning\Quadrant;
use Symfony\Component\Uid\Ulid;

final readonly class CreateTask
{
    public function __construct(
        public string $title,
        public ?string $notes = null,
        public ?Ulid $projectId = null,
        public PlanShortcut $plan = PlanShortcut::None,
        public ?\DateTimeImmutable $planDate = null,
        public ?\DateTimeImmutable $dueOn = null,
        public ?Quadrant $quadrant = null,
    ) {
    }
}
