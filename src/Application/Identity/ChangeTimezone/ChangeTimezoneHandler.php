<?php

declare(strict_types=1);

namespace App\Application\Identity\ChangeTimezone;

use App\Application\Identity\CurrentUser;
use App\Application\Transaction;

final readonly class ChangeTimezoneHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(string $timezone): void
    {
        $this->currentUser->get()->moveTo($timezone);
        $this->transaction->commit();
    }
}
