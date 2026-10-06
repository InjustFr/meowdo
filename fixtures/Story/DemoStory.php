<?php

declare(strict_types=1);

namespace App\Fixtures\Story;

use App\Application\Gamification\PlayerStatsLedger;
use App\Domain\Gamification\Achievement\AchievementReferee;
use App\Domain\Gamification\Achievement\UnlockedAchievement;
use App\Domain\Gamification\Cat;
use App\Domain\Gamification\Coat;
use App\Domain\Gamification\Cosmetic\CosmeticCatalog;
use App\Domain\Gamification\Player;
use App\Domain\Gamification\RewardPolicy;
use App\Domain\Identity\User;
use App\Domain\Planning\Project;
use App\Domain\Planning\ProjectColor;
use App\Domain\Planning\Quadrant;
use App\Domain\Planning\Task;
use App\Domain\Shared\Day;
use App\Fixtures\Factory\CatFactory;
use App\Fixtures\Factory\PlayerFactory;
use App\Fixtures\Factory\ProjectFactory;
use App\Fixtures\Factory\TaskFactory;
use App\Fixtures\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Zenstruck\Foundry\Story;

final class DemoStory extends Story
{
    private User $user;
    private Player $player;
    private Cat $cat;
    private \DateTimeImmutable $now;
    private \DateTimeImmutable $today;

    /** @var array<string, int> */
    private array $ranks = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
        private readonly RewardPolicy $policy,
        private readonly AchievementReferee $referee,
        private readonly PlayerStatsLedger $ledger,
    ) {
    }

    public function build(): void
    {
        $this->now = $this->clock->now();
        $this->user = UserFactory::new()->withPassword('meowdomeowdo')->create([
            'email' => 'demo@meowdo.local',
            'displayName' => 'Demo',
            'timezone' => 'Europe/Paris',
            'now' => $this->now->modify('-3 weeks'),
        ]);
        $this->player = PlayerFactory::createOne(['owner' => $this->user]);
        $this->cat = CatFactory::createOne(['owner' => $this->user, 'name' => 'Mochi', 'coat' => Coat::Ginger]);
        $this->today = $this->user->today($this->now);

        $home = $this->project('Home', ProjectColor::Coral);
        $work = $this->project('Work', ProjectColor::Sky);
        $music = $this->project('Music', ProjectColor::Lavender);
        $health = $this->project('Health', ProjectColor::Catnip);

        $this->done($this->task('Renew the cat insurance', $home, Quadrant::Schedule, planned: -9), -9);
        $this->done($this->task('Send the invoice to the label', $work, Quadrant::DoFirst, planned: -9, due: -8), -9);
        $this->done($this->task('Restring the guitar', $music, Quadrant::Eliminate, planned: -8), -8);
        $this->done($this->task('Book a dentist appointment', $health, Quadrant::Schedule, planned: -3, due: -2), -3);
        $this->done($this->task('Buy cat litter', $home, Quadrant::DoFirst, planned: -3), -3);
        $this->done($this->task('Mix the chorus of "Night Lamp"', $music, Quadrant::Schedule, planned: -2), -2);
        $this->done($this->task('Answer the HR survey', $work, Quadrant::Delegate, planned: -2, due: -1), -2);
        $this->done($this->task('Morning run, 5 km', $health, Quadrant::Schedule, planned: -1), -1);
        $this->done($this->task('Clean the fridge', $home, planned: -1), -1);
        $this->done($this->task('Review pull request #42', $work, Quadrant::DoFirst, planned: -1, due: 0), -1);
        $this->done($this->task('Water the plants', $home, Quadrant::Delegate, planned: 0), 0);

        $this->task('Prepare the sprint demo', $work, Quadrant::DoFirst, planned: 0, due: 0, notes: 'Show the new matrix drag and drop.');
        $this->task('Call the vet about Mochi\'s vaccine', $home, Quadrant::DoFirst, planned: 0);
        $this->task('Stretching, 15 minutes', $health, Quadrant::Schedule, planned: 0);
        $this->task('Pay the electricity bill', null, Quadrant::Delegate, planned: 0, due: 0);
        $this->task('Write lyrics for verse 2', $music, Quadrant::Schedule, planned: -2, due: 4);
        $this->task('Sort the mail pile', $home, Quadrant::Eliminate, planned: -1);
        $this->task('Team retrospective', $work, Quadrant::Delegate, planned: 1);
        $this->task('Grocery run', $home, planned: 1, notes: 'Cat food, oat milk, lemons.');
        $this->task('Yoga class', $health, Quadrant::Schedule, planned: 2);
        $this->task('Record the vocal takes', $music, Quadrant::Schedule, planned: 3, due: 5);
        $this->task('Vacuum the flat', $home, Quadrant::Eliminate, planned: 5);
        $this->task('Quarterly report', $work, Quadrant::Schedule, planned: (int) Day::daysBetween($this->today, $this->today->modify('next monday')), due: 10);
        $this->task('Look into a new microphone', null, Quadrant::Eliminate);
        $this->task('Gift idea for Lea\'s birthday', null, due: 12);
        $this->task('Read "Deep Work"', null, Quadrant::Schedule);
        $this->task('Back up the laptop', $work);

        foreach (['bell-collar', 'yarn-ball'] as $slug) {
            $ownership = $this->player->buy(CosmeticCatalog::get($slug), $this->now);
            $this->entityManager->persist($ownership);
            $this->cat->wear($ownership);
        }
        $this->entityManager->flush();

        foreach ($this->referee->newlyMet($this->ledger->statsOf($this->player), []) as $rule) {
            $achievement = UnlockedAchievement::unlock($this->user, $rule, $this->now);
            $achievement->markSeen($this->now);
            $this->entityManager->persist($achievement);
        }
        $this->entityManager->flush();
    }

    private function project(string $name, ProjectColor $color): Project
    {
        return ProjectFactory::createOne(['owner' => $this->user, 'name' => $name, 'color' => $color, 'now' => $this->now->modify('-3 weeks')]);
    }

    private function task(string $title, ?Project $project, ?Quadrant $quadrant = null, ?int $planned = null, ?int $due = null, ?string $notes = null): Task
    {
        $factory = TaskFactory::new();
        if (null !== $project) {
            $factory = $factory->in($project);
        }
        if (null !== $quadrant) {
            $this->ranks[$quadrant->value] ??= 0;
            $factory = $factory->classified($quadrant, $this->ranks[$quadrant->value]++);
        }
        if (null !== $planned) {
            $factory = $factory->plannedOn($this->day($planned));
        }
        if (null !== $due) {
            $factory = $factory->dueOn($this->day($due));
        }
        $task = $factory->create([
            'owner' => $this->user,
            'title' => $title,
            'now' => $this->moment(min($planned ?? 0, 0) - 2, '09:00'),
        ]);
        $task->describe($notes);

        return $task;
    }

    private function done(Task $task, int $offset): void
    {
        $completedAt = 0 === $offset ? $this->now : $this->moment($offset, '18:30');
        $task->complete($completedAt);
        $task->claimReward($completedAt);
        $day = $this->day($offset);
        $this->player->recordActivity($day);
        $this->player->earn($this->policy->rewardFor($task, $day, $this->player->streak()->current));
    }

    private function day(int $offset): \DateTimeImmutable
    {
        return $this->today->modify(\sprintf('%+d days', $offset));
    }

    private function moment(int $offset, string $time): \DateTimeImmutable
    {
        return new \DateTimeImmutable(Day::format($this->day($offset)).' '.$time, $this->user->zone())
            ->setTimezone($this->now->getTimezone());
    }
}
