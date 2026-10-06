<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

enum CatMood: string
{
    case Purring = 'purring';
    case Idle = 'idle';
    case Sleepy = 'sleepy';

    public const int SLEEPY_AFTER_IDLE_DAYS = 3;

    public static function of(Streak $streak, \DateTimeImmutable $today): self
    {
        $idle = $streak->idleDays($today);

        return match (true) {
            0 === $idle => self::Purring,
            null === $idle, $idle >= self::SLEEPY_AFTER_IDLE_DAYS => self::Sleepy,
            default => self::Idle,
        };
    }
}
