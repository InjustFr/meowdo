<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Gamification;

use App\Application\Gamification\CollectDew\CollectDewHandler;
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
use App\Tests\Support\ActsAsUser;
use App\Tests\Support\FreezesClock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Clock;

final class GreenhouseUseCasesTest extends KernelTestCase
{
    use ActsAsUser;
    use FreezesClock;

    private User $user;

    protected function setUp(): void
    {
        self::freezeAt('2026-10-06 08:00 UTC');
        $this->user = self::actAsNewUser();
    }

    public function testANewGreenhouse(): void
    {
        $greenhouse = $this->greenhouse();

        self::assertSame(['2026-10-06T08:00:00+00:00', 0, 0, 0, 0, 100, 0, null, 2, 12], [$greenhouse->asOf, $greenhouse->dew, $greenhouse->dewGathered, $greenhouse->tank, $greenhouse->tankMilli, $greenhouse->capacity, $greenhouse->rateMilliPerHour, $greenhouse->fullAt, $greenhouse->wateringHours, $greenhouse->maxPots]);
        self::assertSame([[1, null], [2, null]], array_map(static fn (PotView $pot): array => [$pot->number, $pot->species], $greenhouse->pots));
        self::assertSame(
            [['glasshouse', 1, 11, 2, 3, 80], ['condenser', 1, 10, 100, 160, 60], ['misters', 0, 10, 0, 10, 120], ['rain_barrel', 0, 4, 2, 3, 200]],
            array_map(static fn (FacilityView $facility): array => [$facility->id, $facility->level, $facility->maxLevel, $facility->effect, $facility->nextEffect, $facility->cost], $greenhouse->facilities),
        );
        self::assertSame([200, \count(SpeciesCatalog::all())], [$greenhouse->expedition->cost, $greenhouse->expedition->speciesLeft]);
        self::assertSame([], $greenhouse->plantable);
        self::assertSame(
            [['schedule', 12], ['do_first', 6], ['delegate', 3], ['eliminate', 1], [null, 1]],
            array_map(static fn (TaskDewView $dew): array => [$dew->quadrant, $dew->amount], $greenhouse->taskDew),
        );
    }

    public function testAPottedMossFillsTheTankAndCollectingKeepsTheDew(): void
    {
        $this->collect('polytrichum-commune');
        self::getContainer()->get(PlantMossHandler::class)(new PlantMoss(1, 'polytrichum-commune'));

        self::freezeAt('2026-10-06 11:00 UTC');
        $greenhouse = $this->greenhouse();
        self::assertSame([6, 6000, 2000, '2026-10-08T10:00:00+00:00'], [$greenhouse->tank, $greenhouse->tankMilli, $greenhouse->rateMilliPerHour, $greenhouse->fullAt]);
        self::assertSame(['polytrichum-commune', 'common', 2, '2026-10-06T08:00:00+00:00'], [$greenhouse->pots[0]->species, $greenhouse->pots[0]->rarity, $greenhouse->pots[0]->dewPerHour, $greenhouse->pots[0]->plantedAt]);

        $collected = self::getContainer()->get(CollectDewHandler::class)();
        self::getContainer()->get(EntityManagerInterface::class)->clear();

        self::assertSame([6, 6], [$collected->collected, $collected->dew]);
        $greenhouse = $this->greenhouse();
        self::assertSame([6, 6, 0], [$greenhouse->dew, $greenhouse->dewGathered, $greenhouse->tankMilli]);
    }

    public function testPlantableMossesAreTheCollectedOnesByYield(): void
    {
        $this->collect('polytrichum-commune');
        $this->collect('buxbaumia-aphylla');
        $this->collect('thuidium-tamariscinum');
        self::getContainer()->get(PlantMossHandler::class)(new PlantMoss(2, 'thuidium-tamariscinum'));

        self::assertSame(
            [['buxbaumia-aphylla', 'very_rare', 8, null], ['thuidium-tamariscinum', 'uncommon', 3, 2], ['polytrichum-commune', 'common', 2, null]],
            array_map(static fn (PlantableView $moss): array => [$moss->species, $moss->rarity, $moss->dewPerHour, $moss->pot], $this->greenhouse()->plantable),
        );
    }

    public function testUnplantingStopsTheProduction(): void
    {
        $this->collect('buxbaumia-aphylla');
        self::getContainer()->get(PlantMossHandler::class)(new PlantMoss(1, 'buxbaumia-aphylla'));
        self::freezeAt('2026-10-06 09:00 UTC');

        self::getContainer()->get(UnplantMossHandler::class)(1);

        self::freezeAt('2026-10-06 12:00 UTC');
        $greenhouse = $this->greenhouse();
        self::assertSame([8, 0, null], [$greenhouse->tank, $greenhouse->rateMilliPerHour, $greenhouse->pots[0]->species]);
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
        $this->expectExceptionObject(new NotEnoughDew(60, 0));

        self::getContainer()->get(UpgradeFacilityHandler::class)(Facility::Condenser);
    }

    public function testAnExpeditionFindsANewMossForDew(): void
    {
        $this->credit(300);

        $expedition = self::getContainer()->get(LaunchExpeditionHandler::class)();
        self::getContainer()->get(EntityManagerInterface::class)->clear();

        $specimens = self::getContainer()->get(SpecimenRepository::class)->of($this->user);
        self::assertCount(1, $specimens);
        self::assertSame($specimens[0]->species()->slug, $expedition->species->slug);
        self::assertSame(260, $expedition->nextCost);
        $greenhouse = $this->greenhouse();
        self::assertSame([100, 260, \count(SpeciesCatalog::all()) - 1], [$greenhouse->dew, $greenhouse->expedition->cost, $greenhouse->expedition->speciesLeft]);
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
        $this->credit(199);

        $this->expectExceptionObject(new NotEnoughDew(200, 199));

        self::getContainer()->get(LaunchExpeditionHandler::class)();
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
        self::getContainer()->get(GreenhouseRepository::class)->of($this->user)->receive(new DewGain($dew), Clock::get()->now());
        self::getContainer()->get(EntityManagerInterface::class)->flush();
    }
}
