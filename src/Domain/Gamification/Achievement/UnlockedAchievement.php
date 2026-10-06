<?php

declare(strict_types=1);

namespace App\Domain\Gamification\Achievement;

use App\Domain\Identity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'unlocked_achievement')]
#[ORM\UniqueConstraint(name: 'unlocked_achievement_owner_rule', columns: ['owner_id', 'achievement'])]
class UnlockedAchievement
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    private Ulid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $owner;

    #[ORM\Column(length: 40)]
    private string $achievement;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $unlockedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $seenAt = null;

    private function __construct(User $owner, AchievementRule $rule, \DateTimeImmutable $now)
    {
        $this->id = new Ulid();
        $this->owner = $owner;
        $this->achievement = $rule->id();
        $this->unlockedAt = $now;
    }

    public static function unlock(User $owner, AchievementRule $rule, \DateTimeImmutable $now): self
    {
        return new self($owner, $rule, $now);
    }

    public function markSeen(\DateTimeImmutable $now): void
    {
        $this->seenAt ??= $now;
    }

    public function achievement(): string
    {
        return $this->achievement;
    }

    public function unlockedAt(): \DateTimeImmutable
    {
        return $this->unlockedAt;
    }

    public function isSeen(): bool
    {
        return null !== $this->seenAt;
    }
}
