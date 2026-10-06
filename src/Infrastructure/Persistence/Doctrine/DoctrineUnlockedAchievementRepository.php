<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Gamification\Achievement\UnlockedAchievement;
use App\Domain\Gamification\Achievement\UnlockedAchievementRepository;
use App\Domain\Identity\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUnlockedAchievementRepository implements UnlockedAchievementRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function add(UnlockedAchievement $achievement): void
    {
        $this->entityManager->persist($achievement);
    }

    public function of(User $owner): array
    {
        return $this->entityManager->getRepository(UnlockedAchievement::class)->findBy(['owner' => $owner], ['unlockedAt' => 'ASC']);
    }
}
