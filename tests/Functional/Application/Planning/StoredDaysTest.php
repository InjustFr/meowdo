<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Planning;

use App\Application\Gamification\ShowPlayer\ShowPlayerHandler;
use App\Application\Planning\ListTodayTasks\ListTodayTasksHandler;
use App\Domain\Gamification\Reward;
use App\Domain\Planning\PlanShortcut;
use App\Domain\Planning\Quadrant;
use App\Tests\Support\ActsAsUser;
use App\Tests\Support\FreezesClock;
use App\Tests\Support\PlansTasks;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class StoredDaysTest extends KernelTestCase
{
    use ActsAsUser;
    use FreezesClock;
    use PlansTasks;

    private string $serverTimezone;

    protected function setUp(): void
    {
        $this->serverTimezone = date_default_timezone_get();
        date_default_timezone_set('Europe/Paris');
        self::freezeAt('2026-10-06 08:00 UTC');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->serverTimezone);
        parent::tearDown();
    }

    public function testATaskPlannedTodayInParisIsListedInTodayNotEarlier(): void
    {
        self::actAsNewUser('Europe/Paris');
        self::createTask('Planned today', PlanShortcut::Today);
        self::nextRequest();

        $today = self::getContainer()->get(ListTodayTasksHandler::class)();

        self::assertSame(['Planned today'], self::titles($today->today), 'A task planned today, read back from the database, is listed under "From earlier".');
        self::assertSame([], self::titles($today->earlier));
    }

    public function testATaskDoneOnItsDeadlineEarnsTheBonusOnTheNextRequest(): void
    {
        self::actAsNewUser();
        $task = self::createTask('Due today', dueOn: '2026-10-06', quadrant: Quadrant::DoFirst);
        self::nextRequest();

        self::assertEquals(new Reward(26, 7), self::completeTask($task)->reward, 'A deadline read back from the database is compared to a UTC day: the on-time bonus is lost.');
    }

    public function testASecondCompletionTheSameDayKeepsTheStreak(): void
    {
        self::actAsNewUser();
        self::freezeAt('2026-10-05 08:00 UTC');
        self::completeTask(self::createTask('Monday'));
        self::nextRequest();
        self::freezeAt('2026-10-06 08:00 UTC');
        self::completeTask(self::createTask('Tuesday morning'));
        self::nextRequest();
        self::completeTask(self::createTask('Tuesday evening'));

        self::assertSame(2, self::getContainer()->get(ShowPlayerHandler::class)()->streak, 'The last active day read back from the database is not recognised as today: the streak restarts at 1.');
    }

    public function testDoneTodayFollowsATimezoneOtherThanTheServers(): void
    {
        self::actAsNewUser('Asia/Tokyo');
        self::freezeAt('2026-10-06 16:30 UTC');
        self::completeTask(self::createTask('Early Wednesday in Tokyo'));
        self::freezeAt('2026-10-07 08:00 UTC');

        self::assertSame(['Early Wednesday in Tokyo'], self::titles(self::getContainer()->get(ListTodayTasksHandler::class)()->done), 'Completion instants are stored in the server time zone but the day window is bound in the user time zone.');
    }

    private static function nextRequest(): void
    {
        self::getContainer()->get(EntityManagerInterface::class)->clear();
    }
}
