<?php

declare(strict_types=1);

namespace App\Domain\Planning;

enum Quadrant: string
{
    case DoFirst = 'do_first';
    case Schedule = 'schedule';
    case Delegate = 'delegate';
    case Eliminate = 'eliminate';

    public static function of(bool $urgent, bool $important): self
    {
        return match (true) {
            $urgent && $important => self::DoFirst,
            $important => self::Schedule,
            $urgent => self::Delegate,
            default => self::Eliminate,
        };
    }

    public function isUrgent(): bool
    {
        return self::DoFirst === $this || self::Delegate === $this;
    }

    public function isImportant(): bool
    {
        return self::DoFirst === $this || self::Schedule === $this;
    }

    public function priority(): int
    {
        return match ($this) {
            self::DoFirst => 0,
            self::Schedule => 1,
            self::Delegate => 2,
            self::Eliminate => 3,
        };
    }

    public const int UNSORTED_PRIORITY = 4;
}
