<?php

declare(strict_types=1);

namespace App\Application\Gamification\UnplantMoss;

use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;
use Psr\Clock\ClockInterface;

final readonly class UnplantMossHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private GreenhouseRepository $greenhouses,
        private Transaction $transaction,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(int $pot): void
    {
        $this->greenhouses->of($this->currentUser->get())->unplant($pot, $this->clock->now());
        $this->transaction->commit();
    }
}
