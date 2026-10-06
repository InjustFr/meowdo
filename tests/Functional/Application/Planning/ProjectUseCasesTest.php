<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Planning;

use App\Application\Planning\DeleteProject\DeleteProjectHandler;
use App\Application\Planning\EditProject\EditProject;
use App\Application\Planning\EditProject\EditProjectHandler;
use App\Application\Planning\ListInboxTasks\ListInboxTasksHandler;
use App\Application\Planning\ListProjects\ListProjectsHandler;
use App\Application\Planning\ProjectView;
use App\Domain\Planning\Exception\DuplicateProjectName;
use App\Domain\Planning\Exception\EmptyProjectName;
use App\Domain\Planning\ProjectColor;
use App\Tests\Support\ActsAsUser;
use App\Tests\Support\FreezesClock;
use App\Tests\Support\PlansTasks;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Ulid;

final class ProjectUseCasesTest extends KernelTestCase
{
    use ActsAsUser;
    use FreezesClock;
    use PlansTasks;

    protected function setUp(): void
    {
        self::freezeAt('2026-10-06 08:00 UTC');
        self::actAsNewUser();
    }

    public function testCreateProject(): void
    {
        $project = self::createProject('  Home ', ProjectColor::Coral);

        self::assertSame(['Home', 'coral', 0], [$project->name, $project->color, $project->openTasks]);
        self::assertTrue(Ulid::isValid($project->id));
    }

    public function testNamesAreUniquePerUserIgnoringCase(): void
    {
        self::createProject('Home');

        $this->expectExceptionObject(new DuplicateProjectName('HOME'));

        self::createProject(' HOME ');
    }

    public function testAnotherUserMayReuseAName(): void
    {
        self::createProject('Home');
        self::actAsNewUser();

        self::assertSame('Home', self::createProject('Home')->name);
    }

    public function testNameIsRequired(): void
    {
        $this->expectExceptionObject(new EmptyProjectName());

        self::createProject(' ');
    }

    public function testEditProject(): void
    {
        $project = self::createProject('Home');

        $edited = $this->edit($project, 'HOME sweet home', ProjectColor::Mint);
        self::assertSame(['HOME sweet home', 'mint'], [$edited->name, $edited->color]);

        $recased = $this->edit($project, 'home sweet home', ProjectColor::Mint);
        self::assertSame('home sweet home', $recased->name);
    }

    public function testCannotRenameToAnotherProjectsName(): void
    {
        self::createProject('Home');
        $work = self::createProject('Work');

        $this->expectExceptionObject(new DuplicateProjectName('home'));

        $this->edit($work, 'home', ProjectColor::Sky);
    }

    public function testListsProjectsByNameWithTheirOpenTaskCount(): void
    {
        $work = self::createProject('Work');
        $home = self::createProject('Home');
        self::createTask('Laundry', projectId: $home->id);
        self::createTask('Dishes', projectId: $home->id);
        self::completeTask(self::createTask('Vacuum', projectId: $home->id));
        self::createTask('Inbox thought');

        $projects = self::getContainer()->get(ListProjectsHandler::class)();

        self::assertSame([['Home', 2], ['Work', 0]], array_map(static fn (ProjectView $project): array => [$project->name, $project->openTasks], $projects), 'DoctrineTaskQueries::openCountByProject() keys counts by the RFC 4122 form of the project id, ListProjectsHandler looks them up by its base 32 form.');
        self::assertSame($work->id, $projects[1]->id);
    }

    public function testDeletingAProjectMovesItsTasksToTheInbox(): void
    {
        $home = self::createProject('Home');
        self::createTask('Laundry', projectId: $home->id);
        self::createTask('Inbox thought');

        self::getContainer()->get(DeleteProjectHandler::class)(Ulid::fromString($home->id));
        self::getContainer()->get(EntityManagerInterface::class)->clear();

        self::assertSame([], self::getContainer()->get(ListProjectsHandler::class)());
        self::assertEqualsCanonicalizing(['Laundry', 'Inbox thought'], self::titles(self::getContainer()->get(ListInboxTasksHandler::class)()));
    }

    private function edit(ProjectView $project, string $name, ProjectColor $color): ProjectView
    {
        return self::getContainer()->get(EditProjectHandler::class)(new EditProject(Ulid::fromString($project->id), $name, $color));
    }
}
