<?php

declare(strict_types=1);

namespace App\Fixtures\Story;

use App\Application\Gamification\PlayerStatsLedger;
use App\Domain\Gamification\Achievement\AchievementReferee;
use App\Domain\Gamification\Achievement\UnlockedAchievement;
use App\Domain\Gamification\Greenhouse\DewGain;
use App\Domain\Gamification\Greenhouse\DewPolicy;
use App\Domain\Gamification\Greenhouse\Greenhouse;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use App\Domain\Gamification\Herbarium\Specimen;
use App\Domain\Gamification\Player;
use App\Domain\Gamification\RewardPolicy;
use App\Domain\Identity\User;
use App\Domain\Planning\Project;
use App\Domain\Planning\ProjectColor;
use App\Domain\Planning\Quadrant;
use App\Domain\Planning\Recurrence;
use App\Domain\Planning\RecurrenceUnit;
use App\Domain\Planning\Task;
use App\Domain\Shared\Day;
use App\Fixtures\Factory\GreenhouseFactory;
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
    private Greenhouse $greenhouse;
    private DewGain $dew;
    private \DateTimeImmutable $now;
    private \DateTimeImmutable $today;

    /** @var array<string, int> */
    private array $ranks = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
        private readonly RewardPolicy $policy,
        private readonly DewPolicy $dewPolicy,
        private readonly AchievementReferee $referee,
        private readonly PlayerStatsLedger $ledger,
    ) {
    }

    public function build(): void
    {
        $this->now = $this->clock->now();
        $this->user = UserFactory::createOne([
            'accountId' => 'demo',
            'email' => 'demo@mossydew.local',
            'displayName' => 'Demo',
            'timezone' => 'Europe/Paris',
            'now' => $this->now->modify('-3 weeks'),
        ]);
        $this->player = PlayerFactory::createOne(['owner' => $this->user]);
        $this->greenhouse = GreenhouseFactory::createOne(['owner' => $this->user]);
        $this->dew = new DewGain(0);
        $this->today = $this->user->today($this->now);

        $home = $this->project('Home', ProjectColor::Berry);
        $work = $this->project('Work', ProjectColor::Fjord);
        $music = $this->project('Music', ProjectColor::Heather);
        $health = $this->project('Health', ProjectColor::Moss);

        $this->done($this->task('Renew the home insurance', $home, Quadrant::Schedule, planned: -9), -9);
        $this->done($this->task('Send the invoice to the label', $work, Quadrant::DoFirst, planned: -9, due: -8), -9);
        $this->done($this->task('Restring the guitar', $music, Quadrant::Eliminate, planned: -8), -8);
        $this->done($this->task('Book a dentist appointment', $health, Quadrant::Schedule, planned: -3, due: -2), -3);
        $this->done($this->task('Buy potting soil', $home, Quadrant::DoFirst, planned: -3), -3);
        $this->done($this->task('Mix the chorus of "Morning Dew"', $music, Quadrant::Schedule, planned: -2), -2);
        $this->done($this->task('Answer the HR survey', $work, Quadrant::Delegate, planned: -2, due: -1), -2);
        $this->done($this->task('Morning run, 5 km', $health, Quadrant::Schedule, planned: -1), -1);
        $this->done($this->task('Clean the fridge', $home, planned: -1), -1);
        $this->done($this->task('Review pull request #42', $work, Quadrant::DoFirst, planned: -1, due: 0), -1);
        $this->done($this->task('Water the plants', $home, Quadrant::Delegate, planned: 0), 0);

        $this->task('Prepare the sprint demo', $work, Quadrant::DoFirst, planned: 0, due: 0, notes: 'Show the new matrix drag and drop.');
        $this->task('Call the plumber about the leak', $home, Quadrant::DoFirst, planned: 0);
        $this->task('Stretching, 15 minutes', $health, Quadrant::Schedule, planned: 0);
        $this->task('Pay the electricity bill', null, Quadrant::Delegate, planned: 0, due: 0);
        $this->task('Write lyrics for verse 2', $music, Quadrant::Schedule, planned: -2, due: 4);
        $this->task('Sort the mail pile', $home, Quadrant::Eliminate, planned: -1);
        $this->task('Team retrospective', $work, Quadrant::Delegate, planned: 1);
        $this->task('Grocery run', $home, planned: 1, notes: 'Bread, oat milk, lemons.');
        $this->task('Yoga class', $health, Quadrant::Schedule, planned: 2);
        $this->task('Record the vocal takes', $music, Quadrant::Schedule, planned: 3, due: 5);
        $this->task('Vacuum the flat', $home, Quadrant::Eliminate, planned: 5);
        $this->task('Quarterly report', $work, Quadrant::Schedule, planned: (int) Day::daysBetween($this->today, $this->today->modify('next monday')), due: 10);
        $this->task('Water the ferns', $home, Quadrant::Schedule, planned: 0, repeat: new Recurrence(1, RecurrenceUnit::Week));
        $this->task('Pay the rent', null, Quadrant::DoFirst, due: 8, repeat: new Recurrence(1, RecurrenceUnit::Month));
        $this->task('Look into a new microphone', null, Quadrant::Eliminate);
        $this->task('Gift idea for Lea\'s birthday', null, due: 12);
        $this->task('Read "Deep Work"', null, Quadrant::Schedule);
        $this->task('Back up the laptop', $work);
        $cover = $this->task('Cover art for "Morning Dew"', $music, Quadrant::Schedule, due: 9, notes: 'Square, 3000 px, moss and dew drops.');
        $this->subtask($cover, 'Sketch', '09:01');
        $this->subtask($cover, 'Colouring', '09:02', planned: 0);
        $this->subtask($cover, 'Render and export', '09:03');

        $specimens = [];
        foreach (\array_slice(SpeciesCatalog::all(), 0, $this->player->level() - 1) as $species) {
            $specimen = Specimen::collect($this->user, $species, $this->now);
            $this->entityManager->persist($specimen);
            $specimens[] = $specimen;
        }
        $this->tendGreenhouse($specimens, $this->now->modify('-2 days'));
        $this->entityManager->flush();

        foreach ($this->referee->newlyMet($this->ledger->statsOf($this->player), []) as $rule) {
            $achievement = UnlockedAchievement::unlock($this->user, $rule, $this->now);
            $achievement->markSeen($this->now);
            $this->entityManager->persist($achievement);
        }
        $this->entityManager->flush();
    }

    /**
     * @param list<Specimen> $specimens
     */
    private function tendGreenhouse(array $specimens, \DateTimeImmutable $since): void
    {
        $this->greenhouse->receive($this->dew);
        usort($specimens, static fn (Specimen $one, Specimen $other): int => $other->species()->rarity->dewYield() <=> $one->species()->rarity->dewYield());
        foreach (\array_slice($specimens, 0, \count($this->greenhouse->pots())) as $index => $specimen) {
            $this->greenhouse->plant($index + 1, $specimen, $since);
        }
    }

    private function project(string $name, ProjectColor $color): Project
    {
        return ProjectFactory::createOne(['owner' => $this->user, 'name' => $name, 'color' => $color, 'now' => $this->now->modify('-3 weeks')]);
    }

    private function task(string $title, ?Project $project, ?Quadrant $quadrant = null, ?int $planned = null, ?int $due = null, ?string $notes = null, ?Recurrence $repeat = null): Task
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
        if (null !== $repeat) {
            $factory = $factory->repeating($repeat);
        }
        $task = $factory->create([
            'owner' => $this->user,
            'title' => $title,
            'now' => $this->moment(min($planned ?? 0, 0) - 2, '09:00'),
        ]);
        $task->describe($notes);

        return $task;
    }

    private function subtask(Task $parent, string $title, string $time, ?int $planned = null): Task
    {
        $subtask = $parent->addSubtask($title, $this->moment(-2, $time));
        if (null !== $planned) {
            $subtask->planFor($this->day($planned));
        }
        $this->entityManager->persist($subtask);

        return $subtask;
    }

    private function done(Task $task, int $offset): void
    {
        $completedAt = 0 === $offset ? $this->now : $this->moment($offset, '18:30');
        $task->complete($completedAt);
        $task->claimReward($completedAt);
        $day = $this->day($offset);
        $this->player->recordActivity($day);
        $this->player->earn($this->policy->rewardFor($task, $day, $this->player->streak()->current));
        $this->dew = $this->dew->plus($this->dewPolicy->dewFor($task, $this->greenhouse));
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
