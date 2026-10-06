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
    ) {
    }

    public function __invoke(Ulid $id): CompletionView
    {
        $task = $this->tasks->get($id);
        $today = $this->today->date();
        $task->complete($this->today->now());

        $reward = null;
        $leveledUpTo = null;
        if ($task->claimReward($this->today->now())) {
            $player = $this->players->of($this->currentUser->get());
            $player->recordActivity($today);
            $reward = $this->policy->rewardFor($task, $today, $player->streak()->current);
            $leveledUpTo = $player->earn($reward);
        }
        $this->transaction->commit();
        ($this->achievements)();

        return new CompletionView(TaskView::of($task), $reward, $leveledUpTo, ($this->showPlayer)());
    }
}
