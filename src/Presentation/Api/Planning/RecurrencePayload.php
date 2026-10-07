<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Domain\Planning\Recurrence;
use App\Domain\Planning\RecurrenceUnit;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RecurrencePayload
{
    public function __construct(
        #[Assert\Range(notInRangeMessage: 'recurrence.interval.range', min: Recurrence::MIN_INTERVAL, max: Recurrence::MAX_INTERVAL)]
        public int $interval,
        public RecurrenceUnit $unit,
    ) {
    }

    public function toRecurrence(): Recurrence
    {
        return new Recurrence($this->interval, $this->unit);
    }
}
