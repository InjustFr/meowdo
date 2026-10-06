<?php

declare(strict_types=1);

namespace App\Domain\Planning;

use App\Domain\Planning\Exception\MissingPlanDate;
use App\Domain\Shared\Day;

enum PlanShortcut: string
{
    case Today = 'today';
    case Tomorrow = 'tomorrow';
    case NextWeek = 'next_week';
    case Date = 'date';
    case None = 'none';

    public function resolve(\DateTimeImmutable $today, ?\DateTimeImmutable $date = null): ?\DateTimeImmutable
    {
        $today = Day::normalize($today);

        return match ($this) {
            self::Today => $today,
            self::Tomorrow => $today->modify('+1 day'),
            self::NextWeek => $today->modify('next monday'),
            self::Date => null === $date ? throw new MissingPlanDate() : Day::normalize($date),
            self::None => null,
        };
    }
}
