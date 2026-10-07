<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Planning;

use App\Domain\Identity\User;
use App\Domain\Planning\Exception\EmptyTaskTitle;
use App\Domain\Planning\Exception\ProjectOfAnotherOwner;
use App\Domain\Planning\Exception\TaskAlreadyDone;
use App\Domain\Planning\Exception\TaskNotDone;
use App\Domain\Planning\Project;
use App\Domain\Planning\ProjectColor;
use App\Domain\Planning\Quadrant;
use App\Domain\Planning\Task;
use App\Domain\Shared\Day;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
    private const string NOW = '2026-10-06 09:00:00';

    #[DataProvider('blankTitles')]
    public function testTitleIsRequired(string $title): void
    {
        $this->expectExceptionObject(new EmptyTaskTitle());

        $this->task($title);
    }

    /** @return iterable<array{string}> */
    public static function blankTitles(): iterable
    {
        yield 'empty' => [''];
        yield 'spaces' => ['   '];
        yield 'new lines and tabs' => ["\n\t "];
    }

    public function testTitleWhitespaceIsCollapsed(): void
    {
        $task = $this->task("  Water   the\tfern \n tonight ");

        self::assertSame('Water the fern tonight', $task->title());

        $task->rename(" Water \n\n the fern ");

        self::assertSame('Water the fern', $task->title());
    }

    public function testTitleIsCutAtMaxLength(): void
    {
        self::assertSame(Task::MAX_TITLE_LENGTH, mb_strlen($this->task(str_repeat('é', Task::MAX_TITLE_LENGTH + 20))->title()));
    }

    public function testBlankNotesAreCleared(): void
    {
        $task = $this->task('Vet');

        $task->describe('  Bring the booklet ');
        self::assertSame('Bring the booklet', $task->notes());

        $task->describe('   ');
        self::assertNull($task->notes());
    }

    public function testPlanningAndDeadlineAreDays(): void
    {
        $task = $this->task('Vet');

        $task->planFor(new \DateTimeImmutable('2026-10-08 17:45', new \DateTimeZone('Europe/Paris')));
        $task->dueBy(new \DateTimeImmutable('2026-10-10 23:59'));

        self::assertEquals(Day::of('2026-10-08'), $task->plannedOn());
        self::assertEquals(Day::of('2026-10-10'), $task->dueOn());

        $task->unplan();
        $task->clearDeadline();

        self::assertNull($task->plannedOn());
        self::assertNull($task->dueOn());
    }

    public function testCompletingTwiceThrows(): void
    {
        $task = $this->task('Vet');
        $task->complete(new \DateTimeImmutable(self::NOW));

        $this->expectExceptionObject(new TaskAlreadyDone('Vet'));

        $task->complete(new \DateTimeImmutable(self::NOW));
    }

    public function testReopeningAnOpenTaskThrows(): void
    {
        $this->expectExceptionObject(new TaskNotDone('Vet'));

        $this->task('Vet')->reopen();
    }

    public function testReopenThenComplete(): void
    {
        $task = $this->task('Vet');
        $task->complete(new \DateTimeImmutable(self::NOW));
        self::assertTrue($task->isDone());

        $task->reopen();
        self::assertFalse($task->isDone());
        self::assertNull($task->completedAt());

        $task->complete(new \DateTimeImmutable('2026-10-07 10:00'));
        self::assertEquals(new \DateTimeImmutable('2026-10-07 10:00'), $task->completedAt());
    }

    public function testRewardIsClaimedOnceEver(): void
    {
        $task = $this->task('Vet');
        $now = new \DateTimeImmutable(self::NOW);

        self::assertFalse($task->claimReward($now));

        $task->complete($now);
        self::assertTrue($task->claimReward($now));
        self::assertFalse($task->claimReward($now));

        $task->reopen();
        $task->complete($now);
        self::assertFalse($task->claimReward($now));
    }

    public function testIsOnTimeUntilTheDeadlineIncluded(): void
    {
        $task = $this->task('Vet');
        self::assertFalse($task->isOnTime(Day::of('2026-10-06')));

        $task->dueBy(Day::of('2026-10-08'));

        self::assertTrue($task->isOnTime(Day::of('2026-10-06')));
        self::assertTrue($task->isOnTime(Day::of('2026-10-08')));
        self::assertFalse($task->isOnTime(Day::of('2026-10-09')));
    }

    public function testClassifyAndUnclassify(): void
    {
        $task = $this->task('Vet');

        $task->classify(Quadrant::Schedule, 3);
        self::assertSame(Quadrant::Schedule, $task->quadrant());
        self::assertSame(3, $task->rank());

        $task->unclassify();
        self::assertNull($task->quadrant());
        self::assertSame(0, $task->rank());
    }

    public function testFileUnderOwnProjectAndDetach(): void
    {
        $owner = $this->user('louis@example.com');
        $task = Task::create($owner, 'Vet', new \DateTimeImmutable(self::NOW));
        $project = Project::create($owner, 'Home', ProjectColor::Berry, new \DateTimeImmutable(self::NOW));

        $task->fileUnder($project);
        self::assertSame($project, $task->project());

        $task->detach();
        self::assertNull($task->project());
    }

    public function testCannotBeFiledUnderAnotherOwnersProject(): void
    {
        $task = Task::create($this->user('louis@example.com'), 'Vet', new \DateTimeImmutable(self::NOW));
        $project = Project::create($this->user('other@example.com'), 'Home', ProjectColor::Berry, new \DateTimeImmutable(self::NOW));

        $this->expectExceptionObject(new ProjectOfAnotherOwner());

        $task->fileUnder($project);
    }

    private function task(string $title): Task
    {
        return Task::create($this->user('louis@example.com'), $title, new \DateTimeImmutable(self::NOW));
    }

    private function user(string $email): User
    {
        return User::invite($email, 'Louis', 'Europe/Paris', new \DateTimeImmutable(self::NOW));
    }
}
