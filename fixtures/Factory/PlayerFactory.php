<?php

declare(strict_types=1);

namespace App\Fixtures\Factory;

use App\Domain\Gamification\Player;
use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/** @extends PersistentObjectFactory<Player> */
final class PlayerFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Player::class;
    }

    protected function defaults(): array
    {
        return ['owner' => UserFactory::new()];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(Instantiator::namedConstructor('start')->disableHydration());
    }
}
