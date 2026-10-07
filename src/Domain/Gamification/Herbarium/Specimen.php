<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Herbarium;

use App\Domain\Identity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'specimen')]
#[ORM\UniqueConstraint(name: 'specimen_owner_species', columns: ['owner_id', 'species'])]
class Specimen
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    private Ulid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $owner;

    #[ORM\Column(length: 60)]
    private string $species;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $collectedAt;

    private function __construct(User $owner, Species $species, \DateTimeImmutable $now)
    {
        $this->id = new Ulid();
        $this->owner = $owner;
        $this->species = $species->slug;
        $this->collectedAt = $now;
    }

    public static function collect(User $owner, Species $species, \DateTimeImmutable $now): self
    {
        return new self($owner, $species, $now);
    }

    public function species(): Species
    {
        return SpeciesCatalog::get($this->species);
    }

    public function owner(): User
    {
        return $this->owner;
    }

    public function collectedAt(): \DateTimeImmutable
    {
        return $this->collectedAt;
    }
}
