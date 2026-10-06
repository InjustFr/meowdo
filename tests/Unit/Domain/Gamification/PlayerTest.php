<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Gamification;

use App\Domain\Gamification\Cosmetic\CosmeticCatalog;
use App\Domain\Gamification\Exception\LevelTooLow;
use App\Domain\Gamification\Exception\NotEnoughCoins;
use App\Domain\Gamification\Player;
use App\Domain\Gamification\Reward;
use App\Domain\Identity\User;
use App\Domain\Shared\Day;
use PHPUnit\Framework\TestCase;

final class PlayerTest extends TestCase
{
    public function testStartsAtLevelOneWithNothing(): void
    {
        $player = $this->player();

        self::assertSame([0, 0, 1, 0], [$player->xp(), $player->coins(), $player->level(), $player->streak()->current]);
    }

    public function testEarnReturnsTheNewLevelOnlyWhenLevellingUp(): void
    {
        $player = $this->player();

        self::assertNull($player->earn(new Reward(60, 15)));
        self::assertSame(2, $player->earn(new Reward(40, 10)));
        self::assertNull($player->earn(new Reward(10, 3)));
        self::assertSame(4, $player->earn(new Reward(500, 125)));
        self::assertSame([610, 153, 4], [$player->xp(), $player->coins(), $player->level()]);
    }

    public function testRecordsActivityInTheStreak(): void
    {
        $player = $this->player();

        $player->recordActivity(Day::of('2026-10-05'));
        $player->recordActivity(Day::of('2026-10-06'));

        self::assertSame(2, $player->streak()->current);
    }

    public function testBuyingDeductsCoinsAndReturnsTheOwnership(): void
    {
        $player = $this->player();
        $player->earn(new Reward(0, 50));

        $ownership = $player->buy(CosmeticCatalog::get('party-hat'), new \DateTimeImmutable('2026-10-06 10:00'));

        self::assertSame(20, $player->coins());
        self::assertSame('party-hat', $ownership->slug());
        self::assertSame($player->owner(), $ownership->owner());
    }

    public function testCannotBuyBelowTheItemLevel(): void
    {
        $player = $this->player();
        $player->earn(new Reward(0, 1_000));

        $this->expectExceptionObject(new LevelTooLow('crown', 8));

        $player->buy(CosmeticCatalog::get('crown'), new \DateTimeImmutable());
    }

    public function testCannotBuyWithoutEnoughCoins(): void
    {
        $player = $this->player();
        $player->earn(new Reward(0, 29));

        $this->expectExceptionObject(new NotEnoughCoins('party-hat', 30, 29));

        $player->buy(CosmeticCatalog::get('party-hat'), new \DateTimeImmutable());
    }

    public function testExactCoinsAreEnough(): void
    {
        $player = $this->player();
        $player->earn(new Reward(0, 30));

        $player->buy(CosmeticCatalog::get('party-hat'), new \DateTimeImmutable());

        self::assertSame(0, $player->coins());
    }

    private function player(): Player
    {
        return Player::start(User::invite('louis@example.com', 'Louis', 'Europe/Paris', new \DateTimeImmutable('2026-10-06 09:00')));
    }
}
