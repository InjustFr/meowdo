<?php

declare(strict_types=1);

namespace App\Domain\Planning;

enum RecurrenceUnit: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
}
