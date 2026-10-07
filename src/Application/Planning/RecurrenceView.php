<?php

declare(strict_types=1);

namespace App\Application\Planning;

use App\Domain\Planning\Recurrence;

final readonly class RecurrenceView
{
    private function __construct(
        public int $interval,
        public string $unit,
    ) {
    }

    public static function of(?Recurrence $recurrence): ?self
    {
        return null === $recurrence ? null : new self($recurrence->interval, $recurrence->unit->value);
    }
}
