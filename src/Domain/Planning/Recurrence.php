<?php

declare(strict_types=1);

namespace App\Domain\Planning;

use App\Domain\Planning\Exception\InvalidRecurrence;
use App\Domain\Shared\Day;

final readonly class Recurrence
{
    public const int MIN_INTERVAL = 1;
    public const int MAX_INTERVAL = 365;

    public function __construct(
        public int $interval,
        public RecurrenceUnit $unit,
    ) {
        if ($interval < self::MIN_INTERVAL || $interval > self::MAX_INTERVAL) {
            throw new InvalidRecurrence($interval);
        }
    }

    public function next(\DateTimeImmutable $day): \DateTimeImmutable
    {
        $day = Day::normalize($day);

        return match ($this->unit) {
            RecurrenceUnit::Day => $day->modify(\sprintf('+%d days', $this->interval)),
            RecurrenceUnit::Week => $day->modify(\sprintf('+%d days', 7 * $this->interval)),
            RecurrenceUnit::Month => self::addMonths($day, $this->interval),
            RecurrenceUnit::Year => self::addMonths($day, 12 * $this->interval),
        };
    }

    private static function addMonths(\DateTimeImmutable $day, int $months): \DateTimeImmutable
    {
        $target = (int) $day->format('Y') * 12 + (int) $day->format('n') - 1 + $months;
        $firstOfMonth = Day::of(\sprintf('%04d-%02d-01', intdiv($target, 12), $target % 12 + 1));

        return Day::of(\sprintf('%s-%02d', $firstOfMonth->format('Y-m'), min((int) $day->format('j'), (int) $firstOfMonth->format('t'))));
    }
}
