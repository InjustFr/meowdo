<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Gamification\Greenhouse\Greenhouse;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;
use App\Domain\Identity\User;
use App\Domain\Shared\Exception\NotFound;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\Types\UlidType;

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

    public function lockedOf(User $owner): Greenhouse
    {
        $greenhouse = $this->entityManager->createQueryBuilder()
            ->select('greenhouse')
            ->from(Greenhouse::class, 'greenhouse')
            ->where('greenhouse.owner = :owner')
            ->setParameter('owner', $owner->id(), UlidType::NAME)
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->setHint(Query::HINT_REFRESH, true)
            ->getOneOrNullResult();

        return $greenhouse instanceof Greenhouse ? $greenhouse : throw new NotFound('greenhouse', (string) $owner->id());
    }
}
