<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Greenhouse;

use App\Domain\Gamification\Exception\FacilityAtMaxLevel;
use App\Domain\Gamification\Exception\MossAlreadyPlanted;
use App\Domain\Gamification\Exception\MossNotCollected;
use App\Domain\Gamification\Exception\NotEnoughDew;
use App\Domain\Gamification\Exception\PotIsEmpty;
use App\Domain\Gamification\Exception\UnknownPot;
use App\Domain\Gamification\Herbarium\Species;
use App\Domain\Gamification\Herbarium\Specimen;
use App\Domain\Identity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'greenhouse')]
class Greenhouse
{
    public const int MILLI = 1000;
    public const int EXPEDITION_BASE_COST = 200;
    public const int EXPEDITION_COST_PER_TRIP = 50;
    public const int EXPEDITION_COST_PER_TRIP_SQUARED = 10;
    private const int SECONDS_PER_HOUR = 3600;
    private const int PERCENT = 100;

    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    private Ulid $id;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private User $owner;

    #[ORM\Column]
    private int $dew = 0;

    #[ORM\Column]
    private int $dewGathered = 0;

    #[ORM\Column]
    private int $tankMilli = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $settledAt;

    #[ORM\Column]
    private int $expeditions = 0;

    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version = 1;

    #[ORM\Embedded(class: Facilities::class, columnPrefix: 'facilities_')]
    private Facilities $facilities;

    /** @var Collection<int, Pot> */
    #[ORM\OneToMany(targetEntity: Pot::class, mappedBy: 'greenhouse', cascade: ['persist'])]
    #[ORM\OrderBy(['number' => 'ASC'])]
    private Collection $pots;

    private function __construct(User $owner, \DateTimeImmutable $now)
    {
        $this->id = new Ulid();
        $this->owner = $owner;
        $this->settledAt = $now;
        $this->facilities = new Facilities();
        $this->pots = new ArrayCollection();
        for ($number = 1; $number <= $this->facilities->effectOf(Facility::Glasshouse); ++$number) {
            $this->pots->add(Pot::make($this, $number));
        }
    }

    public static function open(User $owner, \DateTimeImmutable $now): self
    {
        return new self($owner, $now);
    }

    public function receive(DewGain $gain, \DateTimeImmutable $now): DewGain
    {
        $this->settle($now);
        $mist = min(intdiv(max(0, $this->capacity() * self::MILLI - $this->tankMilli), self::MILLI), $gain->mist);
        $this->tankMilli += $mist * self::MILLI;
        $this->dew += $gain->amount;
        $this->dewGathered += $gain->amount;

        return new DewGain($gain->amount, $gain->watering, $mist);
    }

    public function collect(\DateTimeImmutable $now): int
    {
        $this->settle($now);
        $collected = intdiv($this->tankMilli, self::MILLI);
        $this->tankMilli -= $collected * self::MILLI;
        $this->dew += $collected;
        $this->dewGathered += $collected;

        return $collected;
    }

    public function plant(int $number, Specimen $specimen, \DateTimeImmutable $now): void
    {
        $pot = $this->pot($number);
        $species = $specimen->species();
        if (!$specimen->owner()->id()->equals($this->owner->id())) {
            throw new MossNotCollected($species->slug);
        }
        $current = $this->potOf($species);
        if (null !== $current) {
            throw new MossAlreadyPlanted($current->number());
        }
        $this->settle($now);
        $pot->grow($species, $now);
    }

    public function unplant(int $number, \DateTimeImmutable $now): void
    {
        $pot = $this->pot($number);
        if ($pot->isEmpty()) {
            throw new PotIsEmpty($number);
        }
        $this->settle($now);
        $pot->empty();
    }

    public function upgrade(Facility $facility, \DateTimeImmutable $now): void
    {
        $this->spend($facility->upgradeCost($this->facilities->levelOf($facility) + 1) ?? throw new FacilityAtMaxLevel($facility));
        $this->settle($now);
        $this->facilities = $this->facilities->raised($facility);
        if (Facility::Glasshouse === $facility) {
            $this->pots->add(Pot::make($this, $this->pots->count() + 1));
        }
    }

    public function fundExpedition(): void
    {
        $this->spend($this->expeditionCost());
        ++$this->expeditions;
    }

    public function tankMilliAt(\DateTimeImmutable $now): int
    {
        $seconds = max(0, $now->getTimestamp() - $this->settledAt->getTimestamp());

        return min($this->capacity() * self::MILLI, $this->tankMilli + intdiv($this->ratePerHourMilli() * $seconds, self::SECONDS_PER_HOUR));
    }

    public function tankAt(\DateTimeImmutable $now): int
    {
        return intdiv($this->tankMilliAt($now), self::MILLI);
    }

    public function isFullAt(\DateTimeImmutable $now): bool
    {
        return $this->tankMilliAt($now) >= $this->capacity() * self::MILLI;
    }

    public function fullAt(): ?\DateTimeImmutable
    {
        $missing = $this->capacity() * self::MILLI - $this->tankMilli;
        if ($missing <= 0) {
            return $this->settledAt;
        }
        $rate = $this->ratePerHourMilli();
        if (0 === $rate) {
            return null;
        }

        return $this->settledAt->modify(\sprintf('+%d seconds', intdiv($missing * self::SECONDS_PER_HOUR + $rate - 1, $rate)));
    }

    public function capacity(): int
    {
        return $this->facilities->effectOf(Facility::Condenser);
    }

    public function ratePerHourMilli(): int
    {
        $base = array_sum(array_map(static fn (Pot $pot): int => $pot->dewPerHourMilli(), $this->pots()));

        return intdiv($base * (self::PERCENT + $this->facilities->effectOf(Facility::Misters)), self::PERCENT);
    }

    public function wateringHours(): int
    {
        return $this->facilities->effectOf(Facility::RainBarrel);
    }

    public function expeditionCost(): int
    {
        $trips = $this->expeditions;

        return self::EXPEDITION_BASE_COST + self::EXPEDITION_COST_PER_TRIP * $trips + self::EXPEDITION_COST_PER_TRIP_SQUARED * $trips * $trips;
    }

    public function potOf(Species $species): ?Pot
    {
        foreach ($this->pots() as $pot) {
            if ($pot->holds($species)) {
                return $pot;
            }
        }

        return null;
    }

    /**
     * @return list<Pot>
     */
    public function pots(): array
    {
        return array_values($this->pots->toArray());
    }

    public function id(): Ulid
    {
        return $this->id;
    }

    public function owner(): User
    {
        return $this->owner;
    }

    public function dew(): int
    {
        return $this->dew;
    }

    public function dewGathered(): int
    {
        return $this->dewGathered;
    }

    public function expeditions(): int
    {
        return $this->expeditions;
    }

    public function facilities(): Facilities
    {
        return $this->facilities;
    }

    private function pot(int $number): Pot
    {
        foreach ($this->pots() as $pot) {
            if ($pot->number() === $number) {
                return $pot;
            }
        }

        throw new UnknownPot($number);
    }

    private function spend(int $cost): void
    {
        if ($this->dew < $cost) {
            throw new NotEnoughDew($cost, $this->dew);
        }
        $this->dew -= $cost;
    }

    private function settle(\DateTimeImmutable $now): void
    {
        if ($now <= $this->settledAt) {
            return;
        }
        $this->tankMilli = $this->tankMilliAt($now);
        $this->settledAt = $now;
    }
}
