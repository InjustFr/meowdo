<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Greenhouse;

use App\Domain\Gamification\Herbarium\Species;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'greenhouse_pot')]
#[ORM\UniqueConstraint(name: 'greenhouse_pot_number', columns: ['greenhouse_id', 'number'])]
class Pot
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    private Ulid $id;

    #[ORM\ManyToOne(targetEntity: Greenhouse::class, inversedBy: 'pots')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Greenhouse $greenhouse;

    #[ORM\Column(type: 'smallint')]
    private int $number;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $species = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $plantedAt = null;

    private function __construct(Greenhouse $greenhouse, int $number)
    {
        $this->id = new Ulid();
        $this->greenhouse = $greenhouse;
        $this->number = $number;
    }

    public static function make(Greenhouse $greenhouse, int $number): self
    {
        return new self($greenhouse, $number);
    }

    public function grow(Species $species, \DateTimeImmutable $now): void
    {
        $this->species = $species->slug;
        $this->plantedAt = $now;
    }

    public function empty(): void
    {
        $this->species = null;
        $this->plantedAt = null;
    }

    public function isEmpty(): bool
    {
        return null === $this->species;
    }

    public function holds(Species $species): bool
    {
        return $this->species === $species->slug;
    }

    public function number(): int
    {
        return $this->number;
    }

    public function species(): ?Species
    {
        return null === $this->species ? null : SpeciesCatalog::get($this->species);
    }

    public function plantedAt(): ?\DateTimeImmutable
    {
        return $this->plantedAt;
    }

    public function dewPerHourMilli(): int
    {
        return 1000 * ($this->species()?->rarity->dewPerHour() ?? 0);
    }
}
