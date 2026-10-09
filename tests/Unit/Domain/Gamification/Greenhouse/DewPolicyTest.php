<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification\Greenhouse;

use App\Domain\Gamification\Greenhouse\DewGain;
use App\Domain\Gamification\Greenhouse\DewPolicy;
use App\Domain\Gamification\Greenhouse\Facility;
use App\Domain\Gamification\Greenhouse\Greenhouse;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use App\Domain\Gamification\Herbarium\Specimen;
use App\Domain\Identity\User;
use App\Domain\Planning\Quadrant;
use App\Domain\Planning\Task;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DewPolicyTest extends TestCase
{
    #[DataProvider('baseDew')]
    public function testBaseDewByQuadrantInAnEmptyGreenhouse(?Quadrant $quadrant, DewGain $gain): void
    {
        self::assertEquals($gain, new DewPolicy()->forQuadrant($quadrant, self::greenhouse()));
    }

    /** @return iterable<array{?Quadrant, DewGain}> */
    public static function baseDew(): iterable
    {
        yield 'plant' => [Quadrant::Schedule, new DewGain(12)];
        yield 'water' => [Quadrant::DoFirst, new DewGain(6)];
        yield 'trim' => [Quadrant::Delegate, new DewGain(3)];
        yield 'compost' => [Quadrant::Eliminate, new DewGain(1)];
        yield 'unsorted' => [null, new DewGain(1)];
    }

    #[DataProvider('extras')]
    public function testEachQuadrantGetsItsOwnExtra(int $pots, int $misters, int $rainBarrel, ?Quadrant $quadrant, DewGain $gain): void
    {
        self::assertEquals($gain, new DewPolicy()->forQuadrant($quadrant, self::greenhouse($pots, $misters, $rainBarrel)));
    }

    /** @return iterable<array{int, int, int, ?Quadrant, DewGain}> */
    public static function extras(): iterable
    {
        yield 'plant waters two hours of two commons' => [2, 0, 0, Quadrant::Schedule, new DewGain(20, 8)];
        yield 'water waters one hour of two commons' => [2, 0, 0, Quadrant::DoFirst, new DewGain(10, 4)];
        yield 'trim mists one hour of two commons' => [2, 0, 0, Quadrant::Delegate, new DewGain(3, mist: 4)];
        yield 'compost gets nothing more' => [2, 0, 0, Quadrant::Eliminate, new DewGain(1)];
        yield 'unsorted gets nothing more' => [2, 0, 0, null, new DewGain(1)];
        yield 'rain barrel lengthens the watering' => [1, 0, 1, Quadrant::Schedule, new DewGain(18, 6)];
        yield 'rain barrel lengthens the half watering' => [1, 0, 1, Quadrant::DoFirst, new DewGain(9, 3)];
        yield 'misters raise the watering' => [1, 1, 0, Quadrant::Schedule, new DewGain(16, 4)];
        yield 'half watering is floored' => [1, 1, 0, Quadrant::DoFirst, new DewGain(8, 2)];
        yield 'mist is floored' => [1, 1, 0, Quadrant::Delegate, new DewGain(3, mist: 2)];
    }

    public function testDewForATaskFollowsItsQuadrant(): void
    {
        $now = new \DateTimeImmutable('2026-10-06 09:00');
        $task = Task::create(User::join('account', 'louis@example.com', 'Louis', 'Europe/Paris', $now), 'Vet', $now);
        $task->classify(Quadrant::Schedule, 0);

        self::assertEquals(new DewGain(12), new DewPolicy()->dewFor($task, self::greenhouse()));
    }

    public function testPlantAlwaysPaysStrictlyMostThenWaterThenTrimThenCompost(): void
    {
        $policy = new DewPolicy();
        foreach (range(0, 4) as $rainBarrel) {
            foreach (range(0, 10) as $misters) {
                foreach (range(0, 12) as $pots) {
                    $greenhouse = self::greenhouse($pots, $misters, $rainBarrel);
                    $worth = array_map(
                        static fn (?Quadrant $quadrant): int => $policy->forQuadrant($quadrant, $greenhouse)->amount + $policy->forQuadrant($quadrant, $greenhouse)->mist,
                        [Quadrant::Schedule, Quadrant::DoFirst, Quadrant::Delegate, Quadrant::Eliminate, null],
                    );
                    $label = \sprintf('%d pots, misters %d, rain barrel %d', $pots, $misters, $rainBarrel);
                    self::assertGreaterThan($worth[1], $worth[0], $label);
                    self::assertGreaterThan($worth[2], $worth[1], $label);
                    self::assertGreaterThan($worth[3], $worth[2], $label);
                    self::assertSame([1, 1], [$worth[3], $worth[4]], $label);
                }
            }
        }
    }

    private static function greenhouse(int $pots = 0, int $misters = 0, int $rainBarrel = 0): Greenhouse
    {
        $now = new \DateTimeImmutable('2026-10-06 09:00');
        $owner = User::join('account', 'louis@example.com', 'Louis', 'Europe/Paris', $now);
        $greenhouse = Greenhouse::open($owner, $now);
        $greenhouse->receive(new DewGain(1_000_000), $now);
        foreach ([[Facility::Glasshouse, max(0, $pots - 2)], [Facility::Misters, $misters], [Facility::RainBarrel, $rainBarrel]] as [$facility, $upgrades]) {
            for ($upgrade = 0; $upgrade < $upgrades; ++$upgrade) {
                $greenhouse->upgrade($facility, $now);
            }
        }
        foreach (\array_slice(SpeciesCatalog::all(), 0, $pots) as $index => $species) {
            $greenhouse->plant($index + 1, Specimen::collect($owner, $species, $now), $now);
        }

        return $greenhouse;
    }
}
