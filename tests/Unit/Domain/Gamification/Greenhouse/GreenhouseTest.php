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
        $this->greenhouse = Greenhouse::open($this->owner, $this->now);
    }

    public function testAGreenhouseOpensSmall(): void
    {
        $greenhouse = $this->greenhouse;
        $facilities = $greenhouse->facilities();

        self::assertSame([0, 0, 0], [$greenhouse->dew(), $greenhouse->dewGathered(), $greenhouse->expeditions()]);
        self::assertSame([1, 1, 0, 0], [$facilities->glasshouse, $facilities->condenser, $facilities->misters, $facilities->rainBarrel]);
        self::assertSame([[1, null], [2, null]], $this->pots());
        self::assertSame([100, 0, 2, 200], [$greenhouse->capacity(), $greenhouse->ratePerHourMilli(), $greenhouse->wateringHours(), $greenhouse->expeditionCost()]);
        self::assertSame(0, $greenhouse->tankMilliAt($this->later('+1 day')));
        self::assertNull($greenhouse->fullAt());
        self::assertSame($this->owner, $greenhouse->owner());
    }

    public function testPottedMossesFillTheTankHourByHour(): void
    {
        $this->plant(1, self::COMMON);
        $this->plant(2, self::VERY_RARE);

        self::assertSame(10000, $this->greenhouse->ratePerHourMilli());
        self::assertSame(10000, $this->greenhouse->tankMilliAt($this->later('+1 hour')));
        self::assertSame(15000, $this->greenhouse->tankMilliAt($this->later('+90 minutes')));
        self::assertSame(2, $this->greenhouse->tankMilliAt($this->later('+1 second')));
        self::assertSame(15, $this->greenhouse->tankAt($this->later('+90 minutes')));
    }

    public function testThePastShowsTheSettledTank(): void
    {
        $this->plant(1, self::COMMON);

        self::assertSame(0, $this->greenhouse->tankMilliAt($this->later('-1 hour')));
    }

    public function testTheTankStopsFillingWhenFullAndLosesNothing(): void
    {
        $this->plant(1, self::VERY_RARE);

        self::assertFalse($this->greenhouse->isFullAt($this->later('+12 hours')));
        self::assertEquals($this->later('+45000 seconds'), $this->greenhouse->fullAt());
        self::assertTrue($this->greenhouse->isFullAt($this->later('+45000 seconds')));
        self::assertSame(100000, $this->greenhouse->tankMilliAt($this->later('+3 days')));
        self::assertSame(100, $this->greenhouse->collect($this->later('+3 days')));
    }

    public function testFullAtIsRoundedUpToTheSecond(): void
    {
        $this->plant(1, self::COMMON);
        $this->greenhouse->receive(new DewGain(120), $this->now);
        $this->greenhouse->upgrade(Facility::Misters, $this->now);

        self::assertEquals($this->later('+163637 seconds'), $this->greenhouse->fullAt());
    }

    public function testCollectMovesWholeDewAndKeepsTheFraction(): void
    {
        $this->plant(1, self::COMMON);

        self::assertSame(1, $this->greenhouse->collect($this->later('+45 minutes')));

        self::assertSame([1, 1], [$this->greenhouse->dew(), $this->greenhouse->dewGathered()]);
        self::assertSame(500, $this->greenhouse->tankMilliAt($this->later('+45 minutes')));
        self::assertSame(2500, $this->greenhouse->tankMilliAt($this->later('+105 minutes')));
    }

    public function testReceivingDewCreditsTheBalanceAndMistsTheTank(): void
    {
        $received = $this->greenhouse->receive(new DewGain(20, 8, 3), $this->now);

        self::assertEquals(new DewGain(20, 8, 3), $received);
        self::assertSame([20, 20, 3000], [$this->greenhouse->dew(), $this->greenhouse->dewGathered(), $this->greenhouse->tankMilliAt($this->now)]);
    }

    public function testMistStopsAtTheTankCapacity(): void
    {
        $this->plant(1, self::VERY_RARE);
        $later = $this->later('+12 hours');

        $received = $this->greenhouse->receive(new DewGain(3, mist: 8), $later);

        self::assertEquals(new DewGain(3, mist: 4), $received);
        self::assertSame(100000, $this->greenhouse->tankMilliAt($later));
    }

    public function testMistOnlyTopsUpWholeDew(): void
    {
        $this->plant(1, self::VERY_RARE);
        $later = $this->later('+44775 seconds');

        $received = $this->greenhouse->receive(new DewGain(3, mist: 4), $later);

        self::assertEquals(new DewGain(3, mist: 0), $received);
        self::assertSame(99500, $this->greenhouse->tankMilliAt($later));
    }

    public function testPlantingIntoAnOccupiedPotReplacesItsMoss(): void
    {
        $this->plant(1, self::COMMON);
        $this->plant(1, self::VERY_RARE);

        self::assertSame([[1, self::VERY_RARE], [2, null]], $this->pots());
        self::assertSame(8000, $this->greenhouse->ratePerHourMilli());
        self::assertSame(self::VERY_RARE, $this->greenhouse->potOf(SpeciesCatalog::get(self::VERY_RARE))?->species()?->slug);
        self::assertNull($this->greenhouse->potOf(SpeciesCatalog::get(self::COMMON)));
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

        $this->greenhouse->unplant(1, $this->later('+1 hour'));

        self::assertSame([[1, null], [2, null]], $this->pots());
        self::assertSame(0, $this->greenhouse->dew());
        self::assertSame(2000, $this->greenhouse->tankMilliAt($this->later('+5 hours')));
    }

    public function testAnEmptyPotCannotBeEmptied(): void
    {
        $this->expectExceptionObject(new PotIsEmpty(2));

        $this->greenhouse->unplant(2, $this->now);
    }

    public function testUnknownPotsCannotBeEmptied(): void
    {
        $this->expectExceptionObject(new UnknownPot(7));

        $this->greenhouse->unplant(7, $this->now);
    }

    public function testChangesSettleTheTankAtTheOldRateFirst(): void
    {
        $this->plant(1, self::COMMON);

        $this->plant(2, self::VERY_RARE, '+1 hour');

        self::assertSame(2000, $this->greenhouse->tankMilliAt($this->later('+1 hour')));
        self::assertSame(12000, $this->greenhouse->tankMilliAt($this->later('+2 hours')));
    }

    public function testUpgradingTheCondenserSettlesAtTheOldCapacity(): void
    {
        $this->plant(1, self::VERY_RARE);
        $this->greenhouse->receive(new DewGain(60), $this->now);

        $this->greenhouse->upgrade(Facility::Condenser, $this->later('+20 hours'));

        self::assertSame([0, 160], [$this->greenhouse->dew(), $this->greenhouse->capacity()]);
        self::assertSame(100000, $this->greenhouse->tankMilliAt($this->later('+20 hours')));
        self::assertSame(108000, $this->greenhouse->tankMilliAt($this->later('+21 hours')));
    }

    public function testUpgradingTheGlasshouseAddsAPot(): void
    {
        $this->greenhouse->receive(new DewGain(80), $this->now);

        $this->greenhouse->upgrade(Facility::Glasshouse, $this->now);

        self::assertSame([[1, null], [2, null], [3, null]], $this->pots());
        self::assertSame([0, 2, 80], [$this->greenhouse->dew(), $this->greenhouse->facilities()->glasshouse, $this->greenhouse->dewGathered()]);
    }

    public function testMistersRaiseTheProduction(): void
    {
        $this->plant(1, self::COMMON);
        $this->greenhouse->receive(new DewGain(120), $this->now);

        $this->greenhouse->upgrade(Facility::Misters, $this->now);

        self::assertSame(2200, $this->greenhouse->ratePerHourMilli());
    }

    public function testTheRainBarrelLengthensWatering(): void
    {
        $this->greenhouse->receive(new DewGain(200), $this->now);

        $this->greenhouse->upgrade(Facility::RainBarrel, $this->now);

        self::assertSame(3, $this->greenhouse->wateringHours());
    }

    public function testAnUnaffordableUpgradeChangesNothing(): void
    {
        $this->plant(1, self::COMMON);
        $this->greenhouse->receive(new DewGain(79), $this->now);

        try {
            $this->greenhouse->upgrade(Facility::Glasshouse, $this->later('+1 hour'));
            self::fail('The upgrade should have been refused.');
        } catch (NotEnoughDew $refused) {
            self::assertEquals(new NotEnoughDew(80, 79), $refused);
        }

        self::assertSame([79, 1, 2], [$this->greenhouse->dew(), $this->greenhouse->facilities()->glasshouse, \count($this->greenhouse->pots())]);
        self::assertSame(4000, $this->greenhouse->tankMilliAt($this->later('+2 hours')));
    }

    public function testAFacilityStopsAtItsMaximumLevel(): void
    {
        $this->greenhouse->receive(new DewGain(10_000), $this->now);
        foreach (range(1, 4) as $level) {
            $this->greenhouse->upgrade(Facility::RainBarrel, $this->now);
        }

        $this->expectExceptionObject(new FacilityAtMaxLevel(Facility::RainBarrel));

        $this->greenhouse->upgrade(Facility::RainBarrel, $this->now);
    }

    public function testExpeditionsCostMoreEachTime(): void
    {
        $this->greenhouse->receive(new DewGain(2000), $this->now);

        $costs = [];
        foreach (range(1, 4) as $trip) {
            $costs[] = $this->greenhouse->expeditionCost();
            $this->greenhouse->fundExpedition();
        }

        self::assertSame([200, 260, 340, 440], $costs);
        self::assertSame([760, 4, 2000], [$this->greenhouse->dew(), $this->greenhouse->expeditions(), $this->greenhouse->dewGathered()]);
    }

    public function testAnExpeditionNeedsEnoughDew(): void
    {
        $this->greenhouse->receive(new DewGain(199), $this->now);

        $this->expectExceptionObject(new NotEnoughDew(200, 199));

        $this->greenhouse->fundExpedition();
    }

    private function plant(int $pot, string $slug, string $after = '+0 seconds'): void
    {
        $this->greenhouse->plant($pot, Specimen::collect($this->owner, SpeciesCatalog::get($slug), $this->now), $this->later($after));
    }

    private function later(string $modifier): \DateTimeImmutable
    {
        return $this->now->modify($modifier);
    }

    /**
     * @return list<array{int, ?string}>
     */
    private function pots(): array
    {
        return array_map(static fn (Pot $pot): array => [$pot->number(), $pot->species()?->slug], $this->greenhouse->pots());
    }
}
