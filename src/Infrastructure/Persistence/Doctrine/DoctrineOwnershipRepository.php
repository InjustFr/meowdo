<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Gamification\Cosmetic\Ownership;
use App\Domain\Gamification\Cosmetic\OwnershipRepository;
use App\Domain\Identity\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineOwnershipRepository implements OwnershipRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function add(Ownership $ownership): void
    {
        $this->entityManager->persist($ownership);
    }

    public function find(User $owner, string $slug): ?Ownership
    {
        return $this->entityManager->getRepository(Ownership::class)->findOneBy(['owner' => $owner, 'slug' => $slug]);
    }

    public function slugsOf(User $owner): array
    {
        return array_map(
            static fn (Ownership $ownership): string => $ownership->slug(),
            $this->entityManager->getRepository(Ownership::class)->findBy(['owner' => $owner]),
        );
    }
}
