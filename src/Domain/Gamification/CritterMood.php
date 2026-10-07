<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

enum CritterMood: string
{
    case Lively = 'lively';
    case Idle = 'idle';
    case Dormant = 'dormant';

    public const int DORMANT_AFTER_IDLE_DAYS = 3;

    public static function of(Streak $streak, \DateTimeImmutable $today): self
    {
        $idle = $streak->idleDays($today);

        return match (true) {
            0 === $idle => self::Lively,
            null === $idle, $idle >= self::DORMANT_AFTER_IDLE_DAYS => self::Dormant,
            default => self::Idle,
        };
    }
}
