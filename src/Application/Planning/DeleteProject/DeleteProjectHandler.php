<?php

declare(strict_types=1);

namespace App\Application\Planning\DeleteProject;

use App\Application\Transaction;
use App\Domain\Planning\ProjectRepository;
use Symfony\Component\Uid\Ulid;

final readonly class DeleteProjectHandler
{
    public function __construct(
        private ProjectRepository $projects,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(Ulid $id): void
    {
        $this->projects->remove($this->projects->get($id));
        $this->transaction->commit();
    }
}
