<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Gamification\Greenhouse\Greenhouse;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;
use App\Domain\Identity\User;
use App\Domain\Shared\Exception\NotFound;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineGreenhouseRepository implements GreenhouseRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function add(Greenhouse $greenhouse): void
    {
        $this->entityManager->persist($greenhouse);
    }

    public function of(User $owner): Greenhouse
    {
        return $this->entityManager->getRepository(Greenhouse::class)->findOneBy(['owner' => $owner])
            ?? throw new NotFound('greenhouse', (string) $owner->id());
    }
}
