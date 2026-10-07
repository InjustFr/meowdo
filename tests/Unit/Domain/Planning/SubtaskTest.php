<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Planning;

use App\Domain\Identity\User;
use App\Domain\Planning\Exception\SubtaskCannotHaveSubtasks;
use App\Domain\Planning\Exception\SubtaskCannotRepeat;
use App\Domain\Planning\Exception\SubtaskFollowsParentProject;
use App\Domain\Planning\Exception\TaskHasOpenSubtasks;
use App\Domain\Planning\Project;
use App\Domain\Planning\ProjectColor;
use App\Domain\Planning\Recurrence;
use App\Domain\Planning\RecurrenceUnit;
use App\Domain\Planning\Task;
use App\Domain\Shared\Day;
use PHPUnit\Framework\TestCase;

final class SubtaskTest extends TestCase
{
    private const string NOW = '2026-10-06 09:00:00';

    private User $owner;

    protected function setUp(): void
    {
        $this->owner = User::join('account', 'louis@example.com', 'Louis', 'Europe/Paris', new \DateTimeImmutable(self::NOW));
    }

    public function testASubtaskBelongsToItsParentWithTheSameOwnerAndProject(): void
    {
        $project = $this->project('Drawings');
        $drawing = $this->task('Fox drawing');
        $drawing->fileUnder($project);

        $sketch = $drawing->addSubtask('Sketch', $this->now());

        self::assertSame([$drawing, $this->owner, $project, false], [$sketch->parent(), $sketch->owner(), $sketch->project(), $sketch->isDone()]);
        self::assertSame([$sketch], $drawing->subtasks());
        self::assertSame([1, 1], [$drawing->subtaskCount(), $drawing->openSubtaskCount()]);
    }

    public function testASubtaskCannotHaveSubtasks(): void
    {
        $sketch = $this->task('Fox drawing')->addSubtask('Sketch', $this->now());

        $this->expectExceptionObject(new SubtaskCannotHaveSubtasks('Sketch'));

        $sketch->addSubtask('Pencils', $this->now());
    }

    public function testAParentWithOpenSubtasksCannotBeCompletedByHand(): void
    {
        $drawing = $this->task('Fox drawing');
        $drawing->addSubtask('Sketch', $this->now());

        $this->expectExceptionObject(new TaskHasOpenSubtasks('Fox drawing'));

        $drawing->complete($this->now());
    }

    public function testCompletingTheLastOpenSubtaskCompletesTheParent(): void
    {
        $drawing = $this->task('Fox drawing');
        $sketch = $drawing->addSubtask('Sketch', $this->now());
        $colouring = $drawing->addSubtask('Colouring', $this->now());

        self::assertSame([$sketch], $sketch->complete($this->now()));
        self::assertFalse($drawing->isDone());

        self::assertSame([$colouring, $drawing], $colouring->complete($this->now()));
        self::assertTrue($drawing->isDone());
        self::assertEquals($this->now(), $drawing->completedAt());
        self::assertSame(0, $drawing->openSubtaskCount());
    }

    public function testReopeningASubtaskReopensItsParent(): void
    {
        $drawing = $this->task('Fox drawing');
        $sketch = $drawing->addSubtask('Sketch', $this->now());
        $sketch->complete($this->now());

        $sketch->reopen();

        self::assertFalse($drawing->isDone());
    }

    public function testAddingASubtaskReopensADoneParent(): void
    {
        $drawing = $this->task('Fox drawing');
        $drawing->addSubtask('Sketch', $this->now())->complete($this->now());

        $drawing->addSubtask('Render', $this->now());

        self::assertFalse($drawing->isDone());
    }

    public function testAReopenedParentWithoutOpenSubtasksCanBeCompletedByHand(): void
    {
        $drawing = $this->task('Fox drawing');
        $drawing->addSubtask('Sketch', $this->now())->complete($this->now());
        $drawing->reopen();

        self::assertSame([$drawing], $drawing->complete($this->now()));
    }

    public function testMovingTheParentMovesItsSubtasks(): void
    {
        $drawing = $this->task('Fox drawing');
        $sketch = $drawing->addSubtask('Sketch', $this->now());
        $project = $this->project('Drawings');

        $drawing->fileUnder($project);
        self::assertSame($project, $sketch->project());

        $drawing->detach();
        self::assertNull($sketch->project());
    }

    public function testASubtaskStaysInTheProjectOfItsParent(): void
    {
        $drawing = $this->task('Fox drawing');
        $drawing->fileUnder($this->project('Drawings'));
        $sketch = $drawing->addSubtask('Sketch', $this->now());

        $this->expectExceptionObject(new SubtaskFollowsParentProject('Sketch'));

        $sketch->detach();
    }

    public function testASubtaskMayBeFiledUnderTheProjectItIsAlreadyIn(): void
    {
        $project = $this->project('Drawings');
        $drawing = $this->task('Fox drawing');
        $drawing->fileUnder($project);
        $sketch = $drawing->addSubtask('Sketch', $this->now());

        $sketch->fileUnder($project);

        self::assertSame($project, $sketch->project());
    }

    public function testASubtaskCannotRepeat(): void
    {
        $sketch = $this->task('Fox drawing')->addSubtask('Sketch', $this->now());
        $sketch->repeat(null);

        $this->expectExceptionObject(new SubtaskCannotRepeat('Sketch'));

        $sketch->repeat(new Recurrence(1, RecurrenceUnit::Week));
    }

    public function testTheNextOccurrenceOfARepeatingParentStartsWithOpenCopiesOfItsSubtasks(): void
    {
        $review = $this->task('Weekly review');
        $inbox = $review->addSubtask('Empty the inbox', $this->now());
        $inbox->describe('Mail too');
        $review->addSubtask('Plan the week', $this->now());
        $review->repeat(new Recurrence(1, RecurrenceUnit::Week));
        foreach ($review->subtasks() as $subtask) {
            $subtask->complete($this->now());
        }

        $next = $review->nextOccurrence(Day::of('2026-10-06'), $this->now());

        self::assertNotNull($next);
        self::assertSame(
            [['Empty the inbox', 'Mail too', false, $next], ['Plan the week', null, false, $next]],
            array_map(static fn (Task $subtask): array => [$subtask->title(), $subtask->notes(), $subtask->isDone(), $subtask->parent()], $next->subtasks()),
        );
    }

    private function task(string $title): Task
    {
        return Task::create($this->owner, $title, $this->now());
    }

    private function project(string $name): Project
    {
        return Project::create($this->owner, $name, ProjectColor::Moss, $this->now());
    }

    private function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(self::NOW);
    }
}
