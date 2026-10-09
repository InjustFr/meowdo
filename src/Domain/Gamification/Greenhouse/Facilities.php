<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Greenhouse;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Facilities
{
    public function __construct(
        #[ORM\Column]
        public int $glasshouse = 1,
        #[ORM\Column]
        public int $misters = 0,
        #[ORM\Column]
        public int $rainBarrel = 0,
    ) {
    }

    public function levelOf(Facility $facility): int
    {
        return match ($facility) {
            Facility::Glasshouse => $this->glasshouse,
            Facility::Misters => $this->misters,
            Facility::RainBarrel => $this->rainBarrel,
        };
    }

    public function effectOf(Facility $facility): int
    {
        return $facility->effectAt($this->levelOf($facility));
    }

    public function raised(Facility $facility): self
    {
        return new self(
            $this->glasshouse + (Facility::Glasshouse === $facility ? 1 : 0),
            $this->misters + (Facility::Misters === $facility ? 1 : 0),
            $this->rainBarrel + (Facility::RainBarrel === $facility ? 1 : 0),
        );
    }
}
