<?php

declare(strict_types=1);

namespace App\Domain\Planning;

use App\Domain\Identity\User;
use App\Domain\Planning\Exception\EmptyTaskTitle;
use App\Domain\Planning\Exception\ProjectOfAnotherOwner;
use App\Domain\Planning\Exception\TaskAlreadyDone;
use App\Domain\Planning\Exception\TaskNotDone;
use App\Domain\Shared\Day;
use App\Domain\Shared\OptionalText;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'task')]
#[ORM\Index(name: 'task_owner_planned', columns: ['owner_id', 'planned_on'])]
#[ORM\Index(name: 'task_owner_quadrant', columns: ['owner_id', 'quadrant', 'rank'])]
class Task
{
    public const int MAX_TITLE_LENGTH = 200;

    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    private Ulid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $owner;

    #[ORM\Column(length: self::MAX_TITLE_LENGTH)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Project $project = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $plannedOn = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dueOn = null;

    #[ORM\Column(length: 16, nullable: true, enumType: Quadrant::class)]
    private ?Quadrant $quadrant = null;

    #[ORM\Column]
    private int $rank = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $rewardedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    private function __construct(User $owner, string $title, \DateTimeImmutable $now)
    {
        $this->id = new Ulid();
        $this->owner = $owner;
        $this->title = self::validTitle($title);
        $this->createdAt = $now;
    }

    public static function create(User $owner, string $title, \DateTimeImmutable $now): self
    {
        return new self($owner, $title, $now);
    }

    public function rename(string $title): void
    {
        $this->title = self::validTitle($title);
    }

    public function describe(?string $notes): void
    {
        $this->notes = OptionalText::of($notes);
    }

    public function planFor(\DateTimeImmutable $day): void
    {
        $this->plannedOn = Day::normalize($day);
    }

    public function unplan(): void
    {
        $this->plannedOn = null;
    }

    public function dueBy(\DateTimeImmutable $day): void
    {
        $this->dueOn = Day::normalize($day);
    }

    public function clearDeadline(): void
    {
        $this->dueOn = null;
    }

    public function fileUnder(Project $project): void
    {
        if (!$project->owner()->id()->equals($this->owner->id())) {
            throw new ProjectOfAnotherOwner();
        }
        $this->project = $project;
    }

    public function detach(): void
    {
        $this->project = null;
    }

    public function classify(Quadrant $quadrant, int $rank): void
    {
        $this->quadrant = $quadrant;
        $this->rank = $rank;
    }

    public function unclassify(): void
    {
        $this->quadrant = null;
        $this->rank = 0;
    }

    public function complete(\DateTimeImmutable $now): void
    {
        if ($this->isDone()) {
            throw new TaskAlreadyDone($this->title);
        }
        $this->completedAt = $now;
    }

    public function reopen(): void
    {
        if (!$this->isDone()) {
            throw new TaskNotDone($this->title);
        }
        $this->completedAt = null;
    }

    public function claimReward(\DateTimeImmutable $now): bool
    {
        if (!$this->isDone() || null !== $this->rewardedAt) {
            return false;
        }
        $this->rewardedAt = $now;

        return true;
    }

    public function isDone(): bool
    {
        return null !== $this->completedAt;
    }

    public function isOnTime(\DateTimeImmutable $today): bool
    {
        return null !== $this->dueOn && Day::normalize($today) <= $this->dueOn();
    }

    public function id(): Ulid
    {
        return $this->id;
    }

    public function owner(): User
    {
        return $this->owner;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function project(): ?Project
    {
        return $this->project;
    }

    public function plannedOn(): ?\DateTimeImmutable
    {
        return null === $this->plannedOn ? null : Day::normalize($this->plannedOn);
    }

    public function dueOn(): ?\DateTimeImmutable
    {
        return null === $this->dueOn ? null : Day::normalize($this->dueOn);
    }

    public function quadrant(): ?Quadrant
    {
        return $this->quadrant;
    }

    public function rank(): int
    {
        return $this->rank;
    }

    public function completedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    private static function validTitle(string $title): string
    {
        $title = trim(preg_replace('/\s+/u', ' ', $title) ?? '');
        if ('' === $title) {
            throw new EmptyTaskTitle();
        }

        return mb_substr($title, 0, self::MAX_TITLE_LENGTH);
    }
}
