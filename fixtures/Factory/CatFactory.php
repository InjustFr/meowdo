<?php

declare(strict_types=1);

namespace App\Fixtures\Factory;

use App\Domain\Gamification\Cat;
use App\Domain\Gamification\Coat;
use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/** @extends PersistentObjectFactory<Cat> */
final class CatFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Cat::class;
    }

    protected function defaults(): array
    {
        return [
            'owner' => UserFactory::new(),
            'name' => self::faker()->randomElement(['Mochi', 'Biscuit', 'Pixel', 'Noodle', 'Tofu']),
            'coat' => self::faker()->randomElement(Coat::cases()),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(Instantiator::namedConstructor('adopt')->disableHydration());
    }
}
