<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Planning;

use App\Application\Planning\ListDoneTasks\DoneDayView;
use App\Application\Planning\ListDoneTasks\DoneView;
use App\Application\Planning\ListDoneTasks\ListDoneTasksHandler;
use App\Application\Planning\ReopenTask\ReopenTaskHandler;
use App\Domain\Shared\Day;
use App\Tests\Support\ActsAsUser;
use App\Tests\Support\FreezesClock;
use App\Tests\Support\PlansTasks;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;

final class DoneTasksTest extends KernelTestCase
{
    use ActsAsUser;
    use FreezesClock;
    use PlansTasks;

    public function testDoneTasksAreGroupedByCompletionDayInTheUsersTimezoneNewestFirst(): void
    {
        self::freezeAt('2026-10-06 21:30 UTC');
        self::actAsNewUser('Europe/Paris');
        self::completeAt('Late on Tuesday', '2026-10-06 21:30 UTC');
        self::completeAt('Just after midnight', '2026-10-06 22:30 UTC');
        self::completeAt('Breakfast', '2026-10-07 07:00 UTC');
        self::freezeAt('2026-10-07 08:00 UTC');

        $done = $this->listDone();

        self::assertSame(['2026-10-07' => ['Breakfast', 'Just after midnight'], '2026-10-06' => ['Late on Tuesday']], self::grouped($done));
        self::assertNull($done->older);
    }

    public function testTheWindowCoversThirtyDaysAndPointsToTheNextOlderCompletion(): void
    {
        self::freezeAt('2026-08-01 10:00 UTC');
        self::actAsNewUser('Europe/Paris');
        self::completeAt('Summer', '2026-08-01 10:00 UTC');
        self::completeAt('Just outside', '2026-09-07 12:00 UTC');
        self::completeAt('First day of the window', '2026-09-07 22:30 UTC');
        self::completeAt('Today', '2026-10-07 07:00 UTC');
        self::freezeAt('2026-10-07 08:00 UTC');

        $first = $this->listDone();
        self::assertSame(['2026-10-07' => ['Today'], '2026-09-08' => ['First day of the window']], self::grouped($first));
        self::assertSame('2026-09-08', $first->older);

        $second = $this->listDone((string) $first->older);
        self::assertSame(['2026-09-07' => ['Just outside']], self::grouped($second));
        self::assertSame('2026-08-02', $second->older);

        $third = $this->listDone((string) $second->older);
        self::assertSame(['2026-08-01' => ['Summer']], self::grouped($third));
        self::assertNull($third->older);
    }

    public function testReopenedTasksAndOtherUsersTasksAreNotListed(): void
    {
        self::freezeAt('2026-10-07 08:00 UTC');
        self::actAsNewUser();
        self::completeTask(self::createTask('Someone else’s'));
        self::actAsNewUser();
        $reopened = self::createTask('Reopened');
        self::completeTask($reopened);
        self::getContainer()->get(ReopenTaskHandler::class)(Ulid::fromString($reopened->id));
        self::completeTask(self::createTask('Mine'));

        $done = $this->listDone();

        self::assertSame(['2026-10-07' => ['Mine']], self::grouped($done));
        self::assertNull($done->older);
    }

    private static function completeAt(string $title, string $moment): void
    {
        self::freezeAt($moment);
        self::completeTask(self::createTask($title));
    }

    private function listDone(?string $before = null): DoneView
    {
        return self::getContainer()->get(ListDoneTasksHandler::class)(null === $before ? null : Day::of($before));
    }

    /**
     * @return array<string, list<string>>
     */
    private static function grouped(DoneView $done): array
    {
        return array_combine(
            array_map(static fn (DoneDayView $day): string => $day->date, $done->days),
            array_map(static fn (DoneDayView $day): array => self::titles($day->tasks), $done->days),
        );
    }
}
