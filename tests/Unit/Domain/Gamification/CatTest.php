<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification;

use App\Domain\Gamification\Cat;
use App\Domain\Gamification\Coat;
use App\Domain\Gamification\Cosmetic\CosmeticCatalog;
use App\Domain\Gamification\Cosmetic\Ownership;
use App\Domain\Gamification\Cosmetic\Slot;
use App\Domain\Gamification\Exception\EmptyCatName;
use App\Domain\Identity\User;
use PHPUnit\Framework\TestCase;

final class CatTest extends TestCase
{
    private User $owner;

    protected function setUp(): void
    {
        $this->owner = User::invite('louis@example.com', 'Louis', 'Europe/Paris', new \DateTimeImmutable('2026-10-06 09:00'));
    }

    public function testAdoptedCatWearsNothing(): void
    {
        $cat = Cat::adopt($this->owner, ' Mochi ', Coat::Calico);

        self::assertSame('Mochi', $cat->name());
        self::assertSame(Coat::Calico, $cat->coat());
        self::assertSame(['hat' => null, 'neckwear' => null, 'toy' => null, 'backdrop' => null], $cat->outfit());
    }

    public function testWearsOneItemPerSlot(): void
    {
        $cat = Cat::adopt($this->owner, 'Mochi', Coat::Ginger);

        foreach (['party-hat', 'bow-tie', 'yarn-ball', 'rooftop', 'beanie'] as $slug) {
            $cat->wear($this->owned($slug));
        }

        self::assertSame(['hat' => 'beanie', 'neckwear' => 'bow-tie', 'toy' => 'yarn-ball', 'backdrop' => 'rooftop'], $cat->outfit());
    }

    public function testTakesOffASlot(): void
    {
        $cat = Cat::adopt($this->owner, 'Mochi', Coat::Ginger);
        $cat->wear($this->owned('party-hat'));
        $cat->wear($this->owned('fish'));

        $cat->takeOff(Slot::Hat);
        $cat->takeOff(Slot::Backdrop);

        self::assertSame(['hat' => null, 'neckwear' => null, 'toy' => 'fish', 'backdrop' => null], $cat->outfit());
    }

    public function testNameIsRequired(): void
    {
        $cat = Cat::adopt($this->owner, 'Mochi', Coat::Ginger);

        $this->expectExceptionObject(new EmptyCatName());

        $cat->rename('   ');
    }

    public function testRenameAndRecoat(): void
    {
        $cat = Cat::adopt($this->owner, 'Mochi', Coat::Ginger);

        $cat->rename(str_repeat('n', Cat::MAX_NAME_LENGTH + 4));
        $cat->recoat(Coat::Midnight);

        self::assertSame(Cat::MAX_NAME_LENGTH, mb_strlen($cat->name()));
        self::assertSame(Coat::Midnight, $cat->coat());
    }

    private function owned(string $slug): Ownership
    {
        return Ownership::acquire($this->owner, CosmeticCatalog::get($slug), new \DateTimeImmutable());
    }
}
