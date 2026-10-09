<?php

declare(strict_types=1);

namespace App\Application\Gamification\CollectDew;

use App\Application\Gamification\AchievementCheck;
use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;
use Psr\Clock\ClockInterface;

final readonly class CollectDewHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private GreenhouseRepository $greenhouses,
        private Transaction $transaction,
        private AchievementCheck $achievements,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(): CollectedDew
    {
        $greenhouse = $this->greenhouses->of($this->currentUser->get());
        $collected = $greenhouse->collect($this->clock->now());
        $this->transaction->commit();
        ($this->achievements)();

        return new CollectedDew($collected, $greenhouse->dew());
    }
}
