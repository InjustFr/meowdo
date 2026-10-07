<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification;

use App\Domain\Gamification\Cosmetic\CosmeticCatalog;
use App\Domain\Gamification\Cosmetic\Ownership;
use App\Domain\Gamification\Cosmetic\Slot;
use App\Domain\Gamification\Critter;
use App\Domain\Gamification\Exception\EmptyCritterName;
use App\Domain\Gamification\Tint;
use App\Domain\Identity\User;
use PHPUnit\Framework\TestCase;

final class CritterTest extends TestCase
{
    private User $owner;

    protected function setUp(): void
    {
        $this->owner = User::invite('louis@example.com', 'Louis', 'Europe/Paris', new \DateTimeImmutable('2026-10-06 09:00'));
    }

    public function testAdoptedCritterWearsNothing(): void
    {
        $critter = Critter::adopt($this->owner, ' Pip ', Tint::Sprout);

        self::assertSame('Pip', $critter->name());
        self::assertSame(Tint::Sprout, $critter->tint());
        self::assertSame(['hat' => null, 'neckwear' => null, 'toy' => null, 'backdrop' => null], $critter->outfit());
    }

    public function testWearsOneItemPerSlot(): void
    {
        $critter = Critter::adopt($this->owner, 'Pip', Tint::Rust);

        foreach (['acorn-cap', 'bow-tie', 'pebble', 'pond', 'beanie'] as $slug) {
            $critter->wear($this->owned($slug));
        }

        self::assertSame(['hat' => 'beanie', 'neckwear' => 'bow-tie', 'toy' => 'pebble', 'backdrop' => 'pond'], $critter->outfit());
    }

    public function testTakesOffASlot(): void
    {
        $critter = Critter::adopt($this->owner, 'Pip', Tint::Rust);
        $critter->wear($this->owned('acorn-cap'));
        $critter->wear($this->owned('dewdrop'));

        $critter->takeOff(Slot::Hat);
        $critter->takeOff(Slot::Backdrop);

        self::assertSame(['hat' => null, 'neckwear' => null, 'toy' => 'dewdrop', 'backdrop' => null], $critter->outfit());
    }

    public function testNameIsRequired(): void
    {
        $critter = Critter::adopt($this->owner, 'Pip', Tint::Rust);

        $this->expectExceptionObject(new EmptyCritterName());

        $critter->rename('   ');
    }

    public function testRenameAndRetint(): void
    {
        $critter = Critter::adopt($this->owner, 'Pip', Tint::Rust);

        $critter->rename(str_repeat('n', Critter::MAX_NAME_LENGTH + 4));
        $critter->retint(Tint::Plum);

        self::assertSame(Critter::MAX_NAME_LENGTH, mb_strlen($critter->name()));
        self::assertSame(Tint::Plum, $critter->tint());
    }

    private function owned(string $slug): Ownership
    {
        return Ownership::acquire($this->owner, CosmeticCatalog::get($slug), new \DateTimeImmutable());
    }
}
