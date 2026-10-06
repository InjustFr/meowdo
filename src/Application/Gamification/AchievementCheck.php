<?php

declare(strict_types=1);

namespace App\Application\Gamification;

use App\Application\Identity\CurrentUser;
use App\Application\Transaction;
use App\Domain\Gamification\Achievement\AchievementReferee;
use App\Domain\Gamification\Achievement\UnlockedAchievement;
use App\Domain\Gamification\Achievement\UnlockedAchievementRepository;
use App\Domain\Gamification\PlayerRepository;
use Psr\Clock\ClockInterface;

final readonly class AchievementCheck
{
    public function __construct(
        private CurrentUser $currentUser,
        private PlayerRepository $players,
        private PlayerStatsLedger $ledger,
        private AchievementReferee $referee,
        private UnlockedAchievementRepository $achievements,
        private Transaction $transaction,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(): void
    {
        $user = $this->currentUser->get();
        $stats = $this->ledger->statsOf($this->players->of($user));
        $unlocked = array_map(static fn (UnlockedAchievement $achievement): string => $achievement->achievement(), $this->achievements->of($user));

        $newlyMet = $this->referee->newlyMet($stats, $unlocked);
        foreach ($newlyMet as $rule) {
            $this->achievements->add(UnlockedAchievement::unlock($user, $rule, $this->clock->now()));
        }
        if ([] !== $newlyMet) {
            $this->transaction->commit();
        }
    }
}
