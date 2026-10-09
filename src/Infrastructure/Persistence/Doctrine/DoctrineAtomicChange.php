<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\AtomicChange;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineAtomicChange implements AtomicChange
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function apply(callable $change): mixed
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        try {
            $result = $change();
            $connection->commit();
        } catch (\Throwable $failure) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            throw $failure;
        }

        return $result;
    }
}
