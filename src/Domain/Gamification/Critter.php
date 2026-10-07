<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

use App\Domain\Gamification\Cosmetic\Ownership;
use App\Domain\Gamification\Cosmetic\Slot;
use App\Domain\Gamification\Exception\EmptyCritterName;
use App\Domain\Identity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'critter')]
class Critter
{
    public const int MAX_NAME_LENGTH = 30;

    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    private Ulid $id;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private User $owner;

    #[ORM\Column(length: self::MAX_NAME_LENGTH)]
    private string $name;

    #[ORM\Column(length: 16, enumType: Tint::class)]
    private Tint $tint;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $hat = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $neckwear = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $toy = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $backdrop = null;

    private function __construct(User $owner, string $name, Tint $tint)
    {
        $this->id = new Ulid();
        $this->owner = $owner;
        $this->name = self::validName($name);
        $this->tint = $tint;
    }

    public static function adopt(User $owner, string $name, Tint $tint): self
    {
        return new self($owner, $name, $tint);
    }

    public function rename(string $name): void
    {
        $this->name = self::validName($name);
    }

    public function retint(Tint $tint): void
    {
        $this->tint = $tint;
    }

    public function wear(Ownership $ownership): void
    {
        $item = $ownership->item();
        match ($item->slot) {
            Slot::Hat => $this->hat = $item->slug,
            Slot::Neckwear => $this->neckwear = $item->slug,
            Slot::Toy => $this->toy = $item->slug,
            Slot::Backdrop => $this->backdrop = $item->slug,
        };
    }

    public function takeOff(Slot $slot): void
    {
        match ($slot) {
            Slot::Hat => $this->hat = null,
            Slot::Neckwear => $this->neckwear = null,
            Slot::Toy => $this->toy = null,
            Slot::Backdrop => $this->backdrop = null,
        };
    }

    /**
     * @return array{hat: ?string, neckwear: ?string, toy: ?string, backdrop: ?string}
     */
    public function outfit(): array
    {
        return ['hat' => $this->hat, 'neckwear' => $this->neckwear, 'toy' => $this->toy, 'backdrop' => $this->backdrop];
    }

    public function id(): Ulid
    {
        return $this->id;
    }

    public function owner(): User
    {
        return $this->owner;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function tint(): Tint
    {
        return $this->tint;
    }

    private static function validName(string $name): string
    {
        $name = trim($name);
        if ('' === $name) {
            throw new EmptyCritterName();
        }

        return mb_substr($name, 0, self::MAX_NAME_LENGTH);
    }
}
