<?php

declare(strict_types=1);

namespace App\Application\Planning\CompleteTask;

use App\Application\Gamification\AchievementCheck;
use App\Application\Gamification\CollectSpecies;
use App\Application\Gamification\ShowPlayer\ShowPlayerHandler;
use App\Application\Gamification\SpeciesView;
use App\Application\Identity\CurrentUser;
use App\Application\Planning\TaskView;
use App\Application\Planning\Today;
use App\Application\Transaction;
use App\Domain\Gamification\Herbarium\Species;
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
        private CollectSpecies $collectSpecies,
    ) {
    }

    public function __invoke(Ulid $id): CompletionView
    {
        $task = $this->tasks->get($id);
        $today = $this->today->date();
        $completed = $task->complete($this->today->now());

        $reward = null;
        $leveledUpTo = null;
        $newSpecies = [];
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
            $levelBefore = $player->level();
            $leveledUpTo = $player->earn($reward);
            if (null !== $leveledUpTo) {
                $newSpecies = ($this->collectSpecies)($player->owner(), $leveledUpTo - $levelBefore);
            }
        }
        $this->transaction->commit();
        ($this->achievements)();

        $parent = $task->parent();

        return new CompletionView(
            TaskView::of($task),
            $reward,
            $leveledUpTo,
            array_map(static fn (Species $species): SpeciesView => SpeciesView::of($species), $newSpecies),
            ($this->showPlayer)(),
            null === $parent ? null : TaskView::of($parent),
        );
    }
}
