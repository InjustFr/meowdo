<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Gamification\Herbarium\Specimen;
use App\Domain\Gamification\Herbarium\SpecimenRepository;
use App\Domain\Identity\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineSpecimenRepository implements SpecimenRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function add(Specimen $specimen): void
    {
        $this->entityManager->persist($specimen);
    }

    public function of(User $owner): array
    {
        return $this->entityManager->getRepository(Specimen::class)->findBy(['owner' => $owner], ['collectedAt' => 'ASC', 'id' => 'ASC']);
    }
}
