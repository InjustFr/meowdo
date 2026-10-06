<?php

declare(strict_types=1);

namespace App\Domain\Identity;

use App\Domain\Identity\Exception\EmptyDisplayName;
use App\Domain\Identity\Exception\InvalidEmail;
use App\Domain\Identity\Exception\UnknownTimezone;
use App\Domain\Shared\Day;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'app_user')]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    private Ulid $id;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $passwordHash = null;

    #[ORM\Column(length: 60)]
    private string $displayName;

    #[ORM\Column(length: 64)]
    private string $timezone;

    #[ORM\Column(length: 2, nullable: true, enumType: Language::class)]
    private ?Language $language = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    private function __construct(string $email, string $displayName, string $timezone, \DateTimeImmutable $now)
    {
        $this->id = new Ulid();
        $this->email = self::normalizeEmail($email);
        $this->displayName = self::validDisplayName($displayName);
        $this->timezone = self::validTimezone($timezone);
        $this->createdAt = $now;
    }

    public static function invite(string $email, string $displayName, string $timezone, \DateTimeImmutable $now): self
    {
        return new self($email, $displayName, $timezone, $now);
    }

    public static function normalizeEmail(string $email): string
    {
        $email = mb_strtolower(trim($email));
        if (false === filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmail($email);
        }

        return $email;
    }

    public function changePassword(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function rename(string $displayName): void
    {
        $this->displayName = self::validDisplayName($displayName);
    }

    public function moveTo(string $timezone): void
    {
        $this->timezone = self::validTimezone($timezone);
    }

    public function speak(Language $language): void
    {
        $this->language = $language;
    }

    public function today(\DateTimeImmutable $now): \DateTimeImmutable
    {
        return Day::at($now, $this->zone());
    }

    public function zone(): \DateTimeZone
    {
        return new \DateTimeZone($this->timezone);
    }

    public function id(): Ulid
    {
        return $this->id;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function passwordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function timezone(): string
    {
        return $this->timezone;
    }

    public function language(): ?Language
    {
        return $this->language;
    }

    private static function validDisplayName(string $displayName): string
    {
        $displayName = trim($displayName);
        if ('' === $displayName) {
            throw new EmptyDisplayName();
        }

        return mb_substr($displayName, 0, 60);
    }

    private static function validTimezone(string $timezone): string
    {
        if (!\in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            throw new UnknownTimezone($timezone);
        }

        return $timezone;
    }
}
