<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Gamification\Cat;
use App\Domain\Gamification\CatRepository;
use App\Domain\Identity\User;
use App\Domain\Shared\Exception\NotFound;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCatRepository implements CatRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function add(Cat $cat): void
    {
        $this->entityManager->persist($cat);
    }

    public function of(User $owner): Cat
    {
        return $this->entityManager->getRepository(Cat::class)->findOneBy(['owner' => $owner])
            ?? throw new NotFound('cat', (string) $owner->id());
    }
}
