<?php

declare(strict_types=1);

namespace App\Application\Gamification\MarkAchievementsSeen;

use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\Achievement\UnlockedAchievementRepository;
use Psr\Clock\ClockInterface;

final readonly class MarkAchievementsSeenHandler
{
    public function __construct(
        private CurrentUser $currentUser,
        private UnlockedAchievementRepository $achievements,
        private Transaction $transaction,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(): void
    {
        foreach ($this->achievements->of($this->currentUser->get()) as $achievement) {
            $achievement->markSeen($this->clock->now());
        }
        $this->transaction->commit();
    }
}
