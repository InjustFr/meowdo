<?php

declare(strict_types=1);

namespace App\Fixtures\Factory;

use App\Domain\Gamification\Greenhouse\Greenhouse;
use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/** @extends PersistentObjectFactory<Greenhouse> */
final class GreenhouseFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Greenhouse::class;
    }

    protected function defaults(): array
    {
        return ['owner' => UserFactory::new(), 'now' => new \DateTimeImmutable()];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(Instantiator::namedConstructor('open')->disableHydration());
    }
}
