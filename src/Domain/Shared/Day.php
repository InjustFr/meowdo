<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use App\Domain\Shared\Exception\InvalidDay;

final class Day
{
    public const string FORMAT = 'Y-m-d';

    public static function of(string $day): \DateTimeImmutable
    {
        $parsed = \DateTimeImmutable::createFromFormat('!'.self::FORMAT, $day, new \DateTimeZone('UTC'));
        if (false === $parsed || $parsed->format(self::FORMAT) !== $day) {
            throw new InvalidDay($day);
        }

        return $parsed;
    }

    public static function at(\DateTimeImmutable $instant, \DateTimeZone $zone): \DateTimeImmutable
    {
        return self::of($instant->setTimezone($zone)->format(self::FORMAT));
    }

    public static function normalize(\DateTimeImmutable $day): \DateTimeImmutable
    {
        return self::of($day->format(self::FORMAT));
    }

    public static function format(?\DateTimeImmutable $day): ?string
    {
        return $day?->format(self::FORMAT);
    }

    public static function startOf(\DateTimeImmutable $day, \DateTimeZone $zone): \DateTimeImmutable
    {
        return new \DateTimeImmutable($day->format(self::FORMAT).' 00:00:00', $zone)->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    }

    public static function daysBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): int
    {
        $days = self::normalize($from)->diff(self::normalize($to))->days;

        return (int) $days * ($from > $to ? -1 : 1);
    }
}
