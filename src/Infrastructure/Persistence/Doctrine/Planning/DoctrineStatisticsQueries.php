<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Planning;

use App\Application\Planning\ShowStatistics\StatisticsQueries;
use App\Domain\Planning\Task;
use App\Infrastructure\Persistence\Doctrine\OwnerScope;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final readonly class DoctrineStatisticsQueries implements StatisticsQueries
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OwnerScope $scope,
    ) {
    }

    public function countCompleted(?\DateTimeImmutable $since = null): int
    {
        $query = $this->counted()->andWhere('t.completedAt IS NOT NULL');
        if (null !== $since) {
            $query->andWhere('t.completedAt >= :since')->setParameter('since', $since, 'datetime_immutable');
        }

        return $this->count($query);
    }

    public function countOpen(): int
    {
        return $this->count($this->counted()->andWhere('t.completedAt IS NULL'));
    }

    public function countOverdue(\DateTimeImmutable $today): int
    {
        return $this->count($this->counted()
            ->andWhere('t.completedAt IS NULL')
            ->andWhere('t.dueOn < :today')
            ->setParameter('today', $today, 'date_immutable'));
    }

    private function counted(): QueryBuilder
    {
        return $this->scope->restrict($this->entityManager->createQueryBuilder()->select('COUNT(t.id)')->from(Task::class, 't'), 't');
    }

    private function count(QueryBuilder $query): int
    {
        $count = $query->getQuery()->getSingleScalarResult();

        return is_numeric($count) ? (int) $count : 0;
    }
}
