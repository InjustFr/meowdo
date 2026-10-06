<?php

declare(strict_types=1);

namespace App\Application\Planning\PlanTask;

use App\Domain\Planning\PlanShortcut;
use Symfony\Component\Uid\Ulid;

final readonly class PlanTask
{
    public function __construct(
        public Ulid $id,
        public PlanShortcut $when,
        public ?\DateTimeImmutable $date = null,
    ) {
    }
}
