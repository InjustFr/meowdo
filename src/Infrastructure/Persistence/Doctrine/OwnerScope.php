<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Identity\CurrentUser;
use App\Domain\Identity\User;
use App\Domain\Shared\Exception\NotFound;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

final readonly class OwnerScope
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CurrentUser $currentUser,
    ) {
    }

    public function owner(): User
    {
        return $this->currentUser->get();
    }

    /**
     * @template T of QueryBuilder
     *
     * @param T $query
     *
     * @return T
     */
    public function restrict(QueryBuilder $query, string $alias): QueryBuilder
    {
        $query->andWhere($alias.'.owner = :owner');
        $query->setParameter('owner', $this->owner()->id(), UlidType::NAME);

        return $query;
    }

    /**
     * @template T of object
     *
     * @param class-string<T>       $entity
     * @param array<string, mixed>  $criteria
     * @param array<string, string> $orderBy
     *
     * @return list<T>
     */
    public function findBy(string $entity, array $criteria = [], array $orderBy = []): array
    {
        return $this->entityManager->getRepository($entity)->findBy(['owner' => $this->owner(), ...$criteria], $orderBy);
    }

    /**
     * @template T of object
     *
     * @param class-string<T>      $entity
     * @param array<string, mixed> $criteria
     *
     * @return T|null
     */
    public function findOneBy(string $entity, array $criteria): ?object
    {
        return $this->entityManager->getRepository($entity)->findOneBy(['owner' => $this->owner(), ...$criteria]);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $entity
     *
     * @return T
     */
    public function get(string $entity, Ulid $id, string $kind): object
    {
        return $this->findOneBy($entity, ['id' => $id]) ?? throw new NotFound($kind, (string) $id);
    }
}
