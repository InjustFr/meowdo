<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Cosmetic;

use App\Domain\Identity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'cosmetic_ownership')]
#[ORM\UniqueConstraint(name: 'cosmetic_ownership_owner_slug', columns: ['owner_id', 'slug'])]
class Ownership
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    private Ulid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $owner;

    #[ORM\Column(length: 40)]
    private string $slug;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $acquiredAt;

    private function __construct(User $owner, CosmeticItem $item, \DateTimeImmutable $now)
    {
        $this->id = new Ulid();
        $this->owner = $owner;
        $this->slug = $item->slug;
        $this->acquiredAt = $now;
    }

    public static function acquire(User $owner, CosmeticItem $item, \DateTimeImmutable $now): self
    {
        return new self($owner, $item, $now);
    }

    public function item(): CosmeticItem
    {
        return CosmeticCatalog::get($this->slug);
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function owner(): User
    {
        return $this->owner;
    }
}
