<?php

declare(strict_types=1);

namespace App\Fixtures\Factory;

use App\Domain\Gamification\Critter;
use App\Domain\Gamification\Tint;
use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/** @extends PersistentObjectFactory<Critter> */
final class CritterFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Critter::class;
    }

    protected function defaults(): array
    {
        return [
            'owner' => UserFactory::new(),
            'name' => self::faker()->randomElement(['Pip', 'Tuft', 'Bramble', 'Fern', 'Sorrel']),
            'tint' => self::faker()->randomElement(Tint::cases()),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(Instantiator::namedConstructor('adopt')->disableHydration());
    }
}
