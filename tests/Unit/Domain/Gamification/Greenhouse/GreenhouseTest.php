<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification\Greenhouse;

use App\Domain\Gamification\Exception\FacilityAtMaxLevel;
use App\Domain\Gamification\Exception\MossAlreadyPlanted;
use App\Domain\Gamification\Exception\MossNotCollected;
use App\Domain\Gamification\Exception\NotEnoughDew;
use App\Domain\Gamification\Exception\PotIsEmpty;
use App\Domain\Gamification\Exception\UnknownPot;
use App\Domain\Gamification\Greenhouse\DewGain;
use App\Domain\Gamification\Greenhouse\Facility;
use App\Domain\Gamification\Greenhouse\Greenhouse;
use App\Domain\Gamification\Greenhouse\Pot;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use App\Domain\Gamification\Herbarium\Specimen;
use App\Domain\Identity\User;
use PHPUnit\Framework\TestCase;

final class GreenhouseTest extends TestCase
{
    private const string COMMON = 'polytrichum-commune';
    private const string VERY_RARE = 'buxbaumia-aphylla';

    private \DateTimeImmutable $now;
    private User $owner;
    private Greenhouse $greenhouse;

    protected function setUp(): void
    {
        $this->now = new \DateTimeImmutable('2026-10-06 08:00:00 UTC');
        $this->owner = User::join('account', 'louis@example.com', 'Louis', 'Europe/Paris', $this->now);
        $this->greenhouse = Greenhouse::open($this->owner);
    }

    public function testAGreenhouseOpensSmall(): void
    {
        $greenhouse = $this->greenhouse;
        $facilities = $greenhouse->facilities();

        self::assertSame([0, 0, 0], [$greenhouse->dew(), $greenhouse->dewGathered(), $greenhouse->expeditions()]);
        self::assertSame([1, 0, 0], [$facilities->glasshouse, $facilities->misters, $facilities->rainBarrel]);
        self::assertSame([[1, null], [2, null]], $this->pots());
        self::assertSame([0, 2, 150], [$greenhouse->yieldTenths(), $greenhouse->wateringMultiplier(), $greenhouse->expeditionCost()]);
        self::assertSame($this->owner, $greenhouse->owner());
    }

    public function testPottedMossesAddTheirYieldByRarity(): void
    {
        $this->plant(1, self::COMMON);
        $this->plant(2, self::VERY_RARE);

        self::assertSame(100, $this->greenhouse->yieldTenths());
    }

    public function testReceivingDewCreditsTheBalanceWithEveryPart(): void
    {
        $this->greenhouse->receive(new DewGain(12, 8));
        $this->greenhouse->receive(new DewGain(3, mist: 4));

        self::assertSame([27, 27], [$this->greenhouse->dew(), $this->greenhouse->dewGathered()]);
    }

    public function testPlantingIntoAnOccupiedPotReplacesItsMoss(): void
    {
        $this->plant(1, self::COMMON);
        $this->plant(1, self::VERY_RARE);

        self::assertSame([[1, self::VERY_RARE], [2, null]], $this->pots());
        self::assertSame(80, $this->greenhouse->yieldTenths());
        self::assertSame(self::VERY_RARE, $this->greenhouse->potOf(SpeciesCatalog::get(self::VERY_RARE))?->species()?->slug);
        self::assertNull($this->greenhouse->potOf(SpeciesCatalog::get(self::COMMON)));
    }

    public function testPlantingRemembersWhenTheMossWentIn(): void
    {
        $this->plant(2, self::COMMON);

        self::assertEquals($this->now, $this->greenhouse->pots()[1]->plantedAt());
    }

    public function testAMossGrowsInOnePotAtATime(): void
    {
        $this->plant(1, self::COMMON);

        $this->expectExceptionObject(new MossAlreadyPlanted(1));

        $this->plant(2, self::COMMON);
    }

    public function testOnlyExistingPotsCanBePlanted(): void
    {
        $this->expectExceptionObject(new UnknownPot(3));

        $this->plant(3, self::COMMON);
    }

    public function testOnlyTheOwnersMossesCanBePlanted(): void
    {
        $stranger = User::join('stranger', 'fern@example.com', 'Fern', 'Europe/Paris', $this->now);

        $this->expectExceptionObject(new MossNotCollected(self::COMMON));

        $this->greenhouse->plant(1, Specimen::collect($stranger, SpeciesCatalog::get(self::COMMON), $this->now), $this->now);
    }

    public function testUnplantingEmptiesThePotForFree(): void
    {
        $this->plant(1, self::COMMON);

        $this->greenhouse->unplant(1);

        self::assertSame([[1, null], [2, null]], $this->pots());
        self::assertSame([0, 0], [$this->greenhouse->dew(), $this->greenhouse->yieldTenths()]);
        self::assertNull($this->greenhouse->pots()[0]->plantedAt());
    }

    public function testAnEmptyPotCannotBeEmptied(): void
    {
        $this->expectExceptionObject(new PotIsEmpty(2));

        $this->greenhouse->unplant(2);
    }

    public function testUnknownPotsCannotBeEmptied(): void
    {
        $this->expectExceptionObject(new UnknownPot(7));

        $this->greenhouse->unplant(7);
    }

    public function testUpgradingTheGlasshouseAddsAPot(): void
    {
        $this->greenhouse->receive(new DewGain(80));

        $this->greenhouse->upgrade(Facility::Glasshouse);

        self::assertSame([[1, null], [2, null], [3, null]], $this->pots());
        self::assertSame([0, 2, 80], [$this->greenhouse->dew(), $this->greenhouse->facilities()->glasshouse, $this->greenhouse->dewGathered()]);
    }

    public function testMistersRaiseTheYieldByATenthPerLevel(): void
    {
        $this->plant(1, self::COMMON);
        $this->greenhouse->receive(new DewGain(280));

        $this->greenhouse->upgrade(Facility::Misters);
        self::assertSame(22, $this->greenhouse->yieldTenths());

        $this->greenhouse->upgrade(Facility::Misters);
        self::assertSame(24, $this->greenhouse->yieldTenths());
    }

    public function testTheRainBarrelRaisesTheWateringMultiplier(): void
    {
        $this->greenhouse->receive(new DewGain(150));

        $this->greenhouse->upgrade(Facility::RainBarrel);

        self::assertSame(3, $this->greenhouse->wateringMultiplier());
    }

    public function testAnUnaffordableUpgradeChangesNothing(): void
    {
        $this->plant(1, self::COMMON);
        $this->greenhouse->receive(new DewGain(79));

        try {
            $this->greenhouse->upgrade(Facility::Glasshouse);
            self::fail('The upgrade should have been refused.');
        } catch (NotEnoughDew $refused) {
            self::assertEquals(new NotEnoughDew(80, 79), $refused);
        }

        self::assertSame([79, 1, 2], [$this->greenhouse->dew(), $this->greenhouse->facilities()->glasshouse, \count($this->greenhouse->pots())]);
        self::assertSame(20, $this->greenhouse->yieldTenths());
    }

    public function testAFacilityStopsAtItsMaximumLevel(): void
    {
        $this->greenhouse->receive(new DewGain(10_000));
        foreach (range(1, 4) as $level) {
            $this->greenhouse->upgrade(Facility::RainBarrel);
        }

        $this->expectExceptionObject(new FacilityAtMaxLevel(Facility::RainBarrel));

        $this->greenhouse->upgrade(Facility::RainBarrel);
    }

    public function testExpeditionsCostMoreEachTime(): void
    {
        $this->greenhouse->receive(new DewGain(2000));

        $costs = [];
        foreach (range(1, 4) as $trip) {
            $costs[] = $this->greenhouse->expeditionCost();
            $this->greenhouse->fundExpedition();
        }

        self::assertSame([150, 198, 262, 342], $costs);
        self::assertSame([1048, 4, 2000], [$this->greenhouse->dew(), $this->greenhouse->expeditions(), $this->greenhouse->dewGathered()]);
    }

    public function testAnExpeditionNeedsEnoughDew(): void
    {
        $this->greenhouse->receive(new DewGain(149));

        $this->expectExceptionObject(new NotEnoughDew(150, 149));

        $this->greenhouse->fundExpedition();
    }

    private function plant(int $pot, string $slug): void
    {
        $this->greenhouse->plant($pot, Specimen::collect($this->owner, SpeciesCatalog::get($slug), $this->now), $this->now);
    }

    /**
     * @return list<array{int, ?string}>
     */
    private function pots(): array
    {
        return array_map(static fn (Pot $pot): array => [$pot->number(), $pot->species()?->slug], $this->greenhouse->pots());
    }
}
