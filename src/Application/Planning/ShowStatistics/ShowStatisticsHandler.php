<?php

declare(strict_types=1);

namespace App\Application\Planning\ShowStatistics;

use App\Application\Identity\CurrentUser;
use App\Application\Planning\TaskQueries;
use App\Application\Planning\Today;
use App\Domain\Gamification\PlayerRepository;
use App\Domain\Shared\Day;

final readonly class ShowStatisticsHandler
{
    public const int DAYS = 30;

    public function __construct(
        private StatisticsQueries $statistics,
        private TaskQueries $tasks,
        private PlayerRepository $players,
        private CurrentUser $currentUser,
        private Today $today,
    ) {
    }

    public function __invoke(): StatisticsView
    {
        $user = $this->currentUser->get();
        $zone = $user->zone();
        $today = $this->today->date();
        $first = $today->modify(\sprintf('-%d days', self::DAYS - 1));
        $monday = $today->modify(\sprintf('-%d days', (int) $today->format('N') - 1));
        $recent = new RecentCompletions($this->tasks->completedBetween(Day::startOf($first, $zone), Day::startOf($today->modify('+1 day'), $zone)), $zone);
        $streak = $this->players->of($user)->streak();

        return new StatisticsView(
            (string) Day::format($today),
            new TotalsView(
                $this->statistics->countCompleted(),
                $this->statistics->countCompleted(Day::startOf($monday, $zone)),
                $this->statistics->countCompleted(Day::startOf($today->modify('first day of this month'), $zone)),
                $this->statistics->countOpen(),
                $this->statistics->countOverdue($today),
            ),
            $streak->asOf($today),
            $streak->best,
            $recent->perDay($first, $today),
            $recent->byQuadrant(),
            $recent->byProject(),
            $recent->onTime(),
        );
    }
}
