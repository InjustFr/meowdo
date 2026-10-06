<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

use App\Domain\Gamification\Cosmetic\CosmeticItem;
use App\Domain\Gamification\Cosmetic\Ownership;
use App\Domain\Gamification\Exception\LevelTooLow;
use App\Domain\Gamification\Exception\NotEnoughCoins;
use App\Domain\Identity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'player')]
class Player
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    private Ulid $id;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private User $owner;

    #[ORM\Column]
    private int $xp = 0;

    #[ORM\Column]
    private int $coins = 0;

    #[ORM\Embedded(class: Streak::class, columnPrefix: 'streak_')]
    private Streak $streak;

    private function __construct(User $owner)
    {
        $this->id = new Ulid();
        $this->owner = $owner;
        $this->streak = new Streak();
    }

    public static function start(User $owner): self
    {
        return new self($owner);
    }

    public function recordActivity(\DateTimeImmutable $day): void
    {
        $this->streak = $this->streak->record($day);
    }

    public function earn(Reward $reward): ?int
    {
        $before = $this->level();
        $this->xp += $reward->xp;
        $this->coins += $reward->coins;

        return $this->level() > $before ? $this->level() : null;
    }

    public function buy(CosmeticItem $item, \DateTimeImmutable $now): Ownership
    {
        if ($this->level() < $item->minLevel) {
            throw new LevelTooLow($item->slug, $item->minLevel);
        }
        if ($this->coins < $item->price) {
            throw new NotEnoughCoins($item->slug, $item->price, $this->coins);
        }
        $this->coins -= $item->price;

        return Ownership::acquire($this->owner, $item, $now);
    }

    public function level(): int
    {
        return LevelCurve::levelFor($this->xp);
    }

    public function id(): Ulid
    {
        return $this->id;
    }

    public function owner(): User
    {
        return $this->owner;
    }

    public function xp(): int
    {
        return $this->xp;
    }

    public function coins(): int
    {
        return $this->coins;
    }

    public function streak(): Streak
    {
        return $this->streak;
    }
}
