<?php

declare(strict_types=1);

namespace App\Application\Planning\CompleteTask;

use App\Application\Gamification\AchievementCheck;
use App\Application\Gamification\ShowPlayer\ShowPlayerHandler;
use App\Application\Identity\CurrentUser;
use App\Application\Planning\TaskView;
use App\Application\Planning\Today;
use App\Application\Transaction;
use App\Domain\Gamification\PlayerRepository;
use App\Domain\Gamification\RewardPolicy;
use App\Domain\Planning\TaskRepository;
use Symfony\Component\Uid\Ulid;

final readonly class CompleteTaskHandler
{
    public function __construct(
        private TaskRepository $tasks,
        private PlayerRepository $players,
        private CurrentUser $currentUser,
        private RewardPolicy $policy,
        private Today $today,
        private Transaction $transaction,
        private AchievementCheck $achievements,
        private ShowPlayerHandler $showPlayer,
        private ContinueSeries $continueSeries,
    ) {
    }

    public function __invoke(Ulid $id): CompletionView
    {
        $task = $this->tasks->get($id);
        $today = $this->today->date();
        $completed = $task->complete($this->today->now());

        $reward = null;
        $leveledUpTo = null;
        $player = $this->players->of($this->currentUser->get());
        foreach ($completed as $done) {
            ($this->continueSeries)($done);
            if (!$done->claimReward($this->today->now())) {
                continue;
            }
            $player->recordActivity($today);
            $earned = $this->policy->rewardFor($done, $today, $player->streak()->current);
            $reward = null === $reward ? $earned : $reward->plus($earned);
        }
        if (null !== $reward) {
            $leveledUpTo = $player->earn($reward);
        }
        $this->transaction->commit();
        ($this->achievements)();

        $parent = $task->parent();

        return new CompletionView(TaskView::of($task), $reward, $leveledUpTo, ($this->showPlayer)(), null === $parent ? null : TaskView::of($parent));
    }
}
