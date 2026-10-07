<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Gamification\Critter;
use App\Domain\Gamification\CritterRepository;
use App\Domain\Identity\User;
use App\Domain\Shared\Exception\NotFound;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCritterRepository implements CritterRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function add(Critter $critter): void
    {
        $this->entityManager->persist($critter);
    }

    public function of(User $owner): Critter
    {
        return $this->entityManager->getRepository(Critter::class)->findOneBy(['owner' => $owner])
            ?? throw new NotFound('critter', (string) $owner->id());
    }
}
