<?php

declare(strict_types=1);

namespace App\Application\Planning\DeleteTask;

use App\Application\Transaction;
use App\Domain\Planning\TaskRepository;
use Symfony\Component\Uid\Ulid;

final readonly class DeleteTaskHandler
{
    public function __construct(
        private TaskRepository $tasks,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(Ulid $id): void
    {
        $this->tasks->remove($this->tasks->get($id));
        $this->transaction->commit();
    }
}
