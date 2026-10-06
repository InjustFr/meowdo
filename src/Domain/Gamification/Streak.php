<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

use App\Domain\Shared\Day;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Streak
{
    public function __construct(
        #[ORM\Column]
        public int $current = 0,
        #[ORM\Column]
        public int $best = 0,
        #[ORM\Column(type: 'date_immutable', nullable: true)]
        public ?\DateTimeImmutable $lastActiveOn = null,
    ) {
    }

    public function record(\DateTimeImmutable $day): self
    {
        $day = Day::normalize($day);
        if (null !== $this->lastActiveOn && $this->lastActiveOn >= $day) {
            return $this;
        }
        $current = null !== $this->lastActiveOn && 1 === Day::daysBetween($this->lastActiveOn, $day) ? $this->current + 1 : 1;

        return new self($current, max($this->best, $current), $day);
    }

    public function asOf(\DateTimeImmutable $today): int
    {
        if (null === $this->lastActiveOn || Day::daysBetween($this->lastActiveOn, $today) > 1) {
            return 0;
        }

        return $this->current;
    }

    public function idleDays(\DateTimeImmutable $today): ?int
    {
        return null === $this->lastActiveOn ? null : max(0, Day::daysBetween($this->lastActiveOn, $today));
    }
}
