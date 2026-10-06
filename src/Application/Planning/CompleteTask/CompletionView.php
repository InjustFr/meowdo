<?php

declare(strict_types=1);

namespace App\Application\Planning\CompleteTask;

use App\Application\Gamification\PlayerView;
use App\Application\Planning\TaskView;
use App\Domain\Gamification\Reward;

final readonly class CompletionView
{
    public function __construct(
        public TaskView $task,
        public ?Reward $reward,
        public ?int $leveledUpTo,
        public PlayerView $player,
    ) {
    }
}
