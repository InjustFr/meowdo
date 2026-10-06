<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Gamification\Player;
use App\Domain\Gamification\PlayerRepository;
use App\Domain\Identity\User;
use App\Domain\Shared\Exception\NotFound;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrinePlayerRepository implements PlayerRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function add(Player $player): void
    {
        $this->entityManager->persist($player);
    }

    public function of(User $owner): Player
    {
        return $this->entityManager->getRepository(Player::class)->findOneBy(['owner' => $owner])
            ?? throw new NotFound('player', (string) $owner->id());
    }
}
