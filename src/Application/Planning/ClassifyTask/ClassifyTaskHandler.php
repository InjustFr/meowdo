<?php

declare(strict_types=1);

namespace App\Application\Planning\ClassifyTask;

use App\Application\Gamification\AchievementCheck;
use App\Application\Planning\TaskView;
use App\Application\Transaction;
use App\Domain\Planning\TaskRepository;

final readonly class ClassifyTaskHandler
{
    public function __construct(
        private TaskRepository $tasks,
        private Transaction $transaction,
        private AchievementCheck $achievements,
    ) {
    }

    public function __invoke(ClassifyTask $command): TaskView
    {
        $task = $this->tasks->get($command->id);
        if (null === $command->quadrant) {
            $task->unclassify();
        } elseif ($task->quadrant() !== $command->quadrant) {
            $task->classify($command->quadrant, $this->tasks->nextRankIn($command->quadrant));
        }
        $this->transaction->commit();
        ($this->achievements)();

        return TaskView::of($task);
    }
}
