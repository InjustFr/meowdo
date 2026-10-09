<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Gamification;

use App\Application\Gamification\CollectSpecies;
use App\Application\Gamification\LaunchExpedition\LaunchExpeditionHandler;
use App\Application\Gamification\PlantMoss\PlantMoss;
use App\Application\Gamification\PlantMoss\PlantMossHandler;
use App\Application\Gamification\ShowGreenhouse\FacilityView;
use App\Application\Gamification\ShowGreenhouse\GreenhouseView;
use App\Application\Gamification\ShowGreenhouse\PlantableView;
use App\Application\Gamification\ShowGreenhouse\PotView;
use App\Application\Gamification\ShowGreenhouse\ShowGreenhouseHandler;
use App\Application\Gamification\ShowGreenhouse\TaskDewView;
use App\Application\Gamification\UnplantMoss\UnplantMossHandler;
use App\Application\Gamification\UpgradeFacility\UpgradeFacilityHandler;
use App\Domain\Gamification\Achievement\UnlockedAchievement;
use App\Domain\Gamification\Achievement\UnlockedAchievementRepository;
use App\Domain\Gamification\Exception\HerbariumComplete;
use App\Domain\Gamification\Exception\MossNotCollected;
use App\Domain\Gamification\Exception\NotEnoughDew;
use App\Domain\Gamification\Exception\UnknownSpecies;
use App\Domain\Gamification\Greenhouse\DewGain;
use App\Domain\Gamification\Greenhouse\Facility;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use App\Domain\Gamification\Herbarium\Specimen;
use App\Domain\Gamification\Herbarium\SpecimenRepository;
use App\Domain\Identity\User;
use App\Domain\Planning\Quadrant;
use App\Tests\Support\ActsAsUser;
use App\Tests\Support\FreezesClock;
use App\Tests\Support\PlansTasks;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Clock;

final class GreenhouseUseCasesTest extends KernelTestCase
{
    use ActsAsUser;
    use FreezesClock;
    use PlansTasks;

    private User $user;

    protected function setUp(): void
    {
        self::freezeAt('2026-10-06 08:00 UTC');
        $this->user = self::actAsNewUser();
    }

    public function testANewGreenhouse(): void
    {
        $greenhouse = $this->greenhouse();

        self::assertSame([0, 0, 0, 2, 12], [$greenhouse->dew, $greenhouse->dewGathered, $greenhouse->yieldTenths, $greenhouse->wateringMultiplier, $greenhouse->maxPots]);
        self::assertSame([[1, null], [2, null]], array_map(static fn (PotView $pot): array => [$pot->number, $pot->species], $greenhouse->pots));
        self::assertSame(
            [['glasshouse', 1, 11, 2, 3, 80], ['misters', 0, 10, 0, 10, 100], ['rain_barrel', 0, 4, 2, 3, 150]],
            array_map(static fn (FacilityView $facility): array => [$facility->id, $facility->level, $facility->maxLevel, $facility->effect, $facility->nextEffect, $facility->cost], $greenhouse->facilities),
        );
        self::assertSame([150, \count(SpeciesCatalog::all())], [$greenhouse->expedition->cost, $greenhouse->expedition->speciesLeft]);
        self::assertSame([], $greenhouse->plantable);
        self::assertSame(
            [['schedule', 12], ['do_first', 6], ['delegate', 3], ['eliminate', 1], [null, 1]],
            array_map(static fn (TaskDewView $dew): array => [$dew->quadrant, $dew->amount], $greenhouse->taskDew),
        );
    }

    public function testPottedMossesRaiseWhatEachTaskPays(): void
    {
        $this->collect('polytrichum-commune');
        $this->collect('buxbaumia-aphylla');
        $plant = self::getContainer()->get(PlantMossHandler::class);
        $plant(new PlantMoss(1, 'polytrichum-commune'));
        $plant(new PlantMoss(2, 'buxbaumia-aphylla'));
        self::getContainer()->get(EntityManagerInterface::class)->clear();

        $greenhouse = $this->greenhouse();
        self::assertSame(100, $greenhouse->yieldTenths);
        self::assertSame(['polytrichum-commune', 'common', 2, '2026-10-06T08:00:00+00:00'], [$greenhouse->pots[0]->species, $greenhouse->pots[0]->rarity, $greenhouse->pots[0]->yield, $greenhouse->pots[0]->plantedAt]);
        self::assertSame(
            [['schedule', 12, 20, 0, 32], ['do_first', 6, 10, 0, 16], ['delegate', 3, 0, 10, 13], ['eliminate', 1, 0, 0, 1], [null, 1, 0, 0, 1]],
            array_map(static fn (TaskDewView $dew): array => [$dew->quadrant, $dew->base, $dew->watering, $dew->mist, $dew->amount], $greenhouse->taskDew),
        );
    }

    public function testPlantableMossesAreTheCollectedOnesByYield(): void
    {
        $this->collect('polytrichum-commune');
        $this->collect('buxbaumia-aphylla');
        $this->collect('thuidium-tamariscinum');
        self::getContainer()->get(PlantMossHandler::class)(new PlantMoss(2, 'thuidium-tamariscinum'));

        self::assertSame(
            [['buxbaumia-aphylla', 'very_rare', 8, null], ['thuidium-tamariscinum', 'uncommon', 3, 2], ['polytrichum-commune', 'common', 2, null]],
            array_map(static fn (PlantableView $moss): array => [$moss->species, $moss->rarity, $moss->yield, $moss->pot], $this->greenhouse()->plantable),
        );
    }

    public function testUnplantingTakesTheMossYieldAway(): void
    {
        $this->collect('buxbaumia-aphylla');
        self::getContainer()->get(PlantMossHandler::class)(new PlantMoss(1, 'buxbaumia-aphylla'));

        self::getContainer()->get(UnplantMossHandler::class)(1);
        self::getContainer()->get(EntityManagerInterface::class)->clear();

        $greenhouse = $this->greenhouse();
        self::assertSame([0, null, 12], [$greenhouse->yieldTenths, $greenhouse->pots[0]->species, $greenhouse->taskDew[0]->amount]);
    }

    public function testOnlyCollectedMossesCanBePlanted(): void
    {
        $this->expectExceptionObject(new MossNotCollected('polytrichum-commune'));

        self::getContainer()->get(PlantMossHandler::class)(new PlantMoss(1, 'polytrichum-commune'));
    }

    public function testUnknownMossesCannotBePlanted(): void
    {
        $this->expectExceptionObject(new UnknownSpecies('dandelion'));

        self::getContainer()->get(PlantMossHandler::class)(new PlantMoss(1, 'dandelion'));
    }

    public function testUpgradingAFacilitySpendsDewInstantly(): void
    {
        $this->credit(100);

        self::getContainer()->get(UpgradeFacilityHandler::class)(Facility::Glasshouse);
        self::getContainer()->get(EntityManagerInterface::class)->clear();

        $greenhouse = $this->greenhouse();
        self::assertSame([20, 100, 3], [$greenhouse->dew, $greenhouse->dewGathered, \count($greenhouse->pots)]);
        self::assertSame([2, 140], [$greenhouse->facilities[0]->level, $greenhouse->facilities[0]->cost]);
    }

    public function testAnUnaffordableUpgradeIsRefused(): void
    {
        $this->expectExceptionObject(new NotEnoughDew(150, 0));

        self::getContainer()->get(UpgradeFacilityHandler::class)(Facility::RainBarrel);
    }

    public function testAnExpeditionFindsANewMossForDew(): void
    {
        $this->credit(300);

        $expedition = self::getContainer()->get(LaunchExpeditionHandler::class)();
        self::getContainer()->get(EntityManagerInterface::class)->clear();

        $specimens = self::getContainer()->get(SpecimenRepository::class)->of($this->user);
        self::assertCount(1, $specimens);
        self::assertSame($specimens[0]->species()->slug, $expedition->species->slug);
        self::assertSame(198, $expedition->nextCost);
        $greenhouse = $this->greenhouse();
        self::assertSame([150, 198, 1, \count(SpeciesCatalog::all()) - 1], [$greenhouse->dew, $greenhouse->expedition->cost, $greenhouse->expedition->trips, $greenhouse->expedition->speciesLeft]);
    }

    public function testACompleteHerbariumRefusesExpeditionsForFree(): void
    {
        self::getContainer()->get(CollectSpecies::class)($this->user, \count(SpeciesCatalog::all()));
        $this->credit(300);

        try {
            self::getContainer()->get(LaunchExpeditionHandler::class)();
            self::fail('The expedition should have been refused.');
        } catch (HerbariumComplete $refused) {
            self::assertSame('game.herbarium_complete', $refused->getMessage());
        }
        self::getContainer()->get(EntityManagerInterface::class)->clear();

        self::assertSame([300, 0], [$this->greenhouse()->dew, $this->greenhouse()->expedition->speciesLeft]);
    }

    public function testAnExpeditionNeedsEnoughDew(): void
    {
        $this->credit(149);

        $this->expectExceptionObject(new NotEnoughDew(150, 149));

        self::getContainer()->get(LaunchExpeditionHandler::class)();
    }

    public function testTheFirstExpeditionUnlocksFieldTrip(): void
    {
        $this->credit(150);

        self::getContainer()->get(LaunchExpeditionHandler::class)();

        self::assertSame(['field_trip'], $this->unlocked());
    }

    public function testAGlasshouseAtLevelFiveUnlocksGrowingGlasshouse(): void
    {
        $this->credit(950);
        $upgrade = self::getContainer()->get(UpgradeFacilityHandler::class);

        $upgrade(Facility::Glasshouse);
        $upgrade(Facility::Glasshouse);
        $upgrade(Facility::Glasshouse);
        self::assertSame([], $this->unlocked());

        $upgrade(Facility::Glasshouse);
        self::assertSame(['glasshouse_5'], $this->unlocked());
    }

    public function testTheTaskThatBringsTheThousandthDewUnlocksMorningDew(): void
    {
        $this->credit(985);
        $this->collect('polytrichum-commune');
        self::getContainer()->get(PlantMossHandler::class)(new PlantMoss(1, 'polytrichum-commune'));

        self::completeTask(self::createTask('Compost', quadrant: Quadrant::Eliminate));
        self::assertNotContains('dew_1000', $this->unlocked());

        self::completeTask(self::createTask('Plant', quadrant: Quadrant::Schedule));
        self::assertContains('dew_1000', $this->unlocked());
    }

    public function testASpendWaitsForAndKeepsAChangeSavedMeanwhile(): void
    {
        $this->credit(300);
        $this->changeMeanwhile('UPDATE greenhouse SET dew = dew + 12, dew_gathered = dew_gathered + 12, version = version + 1 WHERE owner_id = ?');

        self::getContainer()->get(LaunchExpeditionHandler::class)();

        self::assertSame([162, 312, 1, 1], [
            $this->stored('SELECT dew FROM greenhouse WHERE owner_id = ?'),
            $this->stored('SELECT dew_gathered FROM greenhouse WHERE owner_id = ?'),
            $this->stored('SELECT expeditions FROM greenhouse WHERE owner_id = ?'),
            $this->stored('SELECT COUNT(*) FROM specimen WHERE owner_id = ?'),
        ]);
    }

    public function testACompletionWaitsForAndKeepsAChangeSavedMeanwhile(): void
    {
        $this->credit(300);
        $task = self::createTask('Compost', quadrant: Quadrant::Eliminate);
        $this->changeMeanwhile('UPDATE greenhouse SET dew = dew - 150, expeditions = expeditions + 1, version = version + 1 WHERE owner_id = ?');

        $completion = self::completeTask($task);

        self::assertSame([1, 151, 301, 1], [
            $completion->dew?->amount,
            $this->stored('SELECT dew FROM greenhouse WHERE owner_id = ?'),
            $this->stored('SELECT dew_gathered FROM greenhouse WHERE owner_id = ?'),
            $this->stored('SELECT expeditions FROM greenhouse WHERE owner_id = ?'),
        ]);
        self::assertNotNull($completion->task->completedAt);
    }

    public function testEveryChangeOfTheGreenhouseWaitsForTheOneBeforeIt(): void
    {
        $this->credit(1000);
        $this->collect('polytrichum-commune');
        $task = self::createTask('Plant', quadrant: Quadrant::Schedule);

        self::assertTrue($this->locksTheGreenhouse(static fn () => self::getContainer()->get(PlantMossHandler::class)(new PlantMoss(1, 'polytrichum-commune'))));
        self::assertTrue($this->locksTheGreenhouse(static fn () => self::getContainer()->get(UnplantMossHandler::class)(1)));
        self::assertTrue($this->locksTheGreenhouse(static fn () => self::getContainer()->get(UpgradeFacilityHandler::class)(Facility::Glasshouse)));
        self::assertTrue($this->locksTheGreenhouse(static fn () => self::getContainer()->get(LaunchExpeditionHandler::class)()));
        self::assertTrue($this->locksTheGreenhouse(static fn () => self::completeTask($task)));
        self::assertFalse($this->locksTheGreenhouse(fn () => $this->greenhouse()));
    }

    public function testTwoPotsNeverHoldTheSameMoss(): void
    {
        $this->collect('polytrichum-commune');
        self::getContainer()->get(GreenhouseRepository::class)->of($this->user)->pots();
        $this->changeMeanwhile("UPDATE greenhouse_pot SET species = 'polytrichum-commune' WHERE number = 1 AND greenhouse_id = (SELECT id FROM greenhouse WHERE owner_id = ?)");

        $this->expectException(UniqueConstraintViolationException::class);

        self::getContainer()->get(PlantMossHandler::class)(new PlantMoss(2, 'polytrichum-commune'));
    }

    private function locksTheGreenhouse(\Closure $change): bool
    {
        $queries = self::getContainer()->get('doctrine.debug_data_holder');
        $queries->reset();
        $change();
        $sql = array_column(array_merge([], ...array_values($queries->getData())), 'sql');

        return [] !== preg_grep('/\bFROM greenhouse\b.*\bFOR UPDATE\b/s', array_filter($sql, is_string(...)));
    }

    private function changeMeanwhile(string $sql): void
    {
        self::getContainer()->get(Connection::class)->executeStatement($sql, [$this->user->id()->toRfc4122()]);
    }

    private function stored(string $sql): int
    {
        $value = self::getContainer()->get(Connection::class)->fetchOne($sql, [$this->user->id()->toRfc4122()]);
        self::assertIsNumeric($value);

        return (int) $value;
    }

    /**
     * @return list<string>
     */
    private function unlocked(): array
    {
        return array_map(
            static fn (UnlockedAchievement $achievement): string => $achievement->achievement(),
            self::getContainer()->get(UnlockedAchievementRepository::class)->of($this->user),
        );
    }

    private function greenhouse(): GreenhouseView
    {
        return self::getContainer()->get(ShowGreenhouseHandler::class)();
    }

    private function collect(string $slug): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist(Specimen::collect($this->user, SpeciesCatalog::get($slug), Clock::get()->now()));
        $entityManager->flush();
    }

    private function credit(int $dew): void
    {
        self::getContainer()->get(GreenhouseRepository::class)->of($this->user)->receive(new DewGain($dew));
        self::getContainer()->get(EntityManagerInterface::class)->flush();
    }
}
