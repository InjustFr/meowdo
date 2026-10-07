<?php

declare(strict_types=1);

namespace App\Application\Identity\SignIn;

use App\Application\Transaction;
use App\Domain\Gamification\Critter;
use App\Domain\Gamification\CritterRepository;
use App\Domain\Gamification\Player;
use App\Domain\Gamification\PlayerRepository;
use App\Domain\Gamification\Tint;
use App\Domain\Identity\Exception\EmailAlreadyTaken;
use App\Domain\Identity\Exception\InvalidEmail;
use App\Domain\Identity\User;
use App\Domain\Identity\UserRepository;
use Psr\Clock\ClockInterface;

final readonly class SignInHandler
{
    private const string DEFAULT_TIMEZONE = 'Europe/Paris';
    private const string CRITTER_NAME = 'Pip';

    public function __construct(
        private UserRepository $users,
        private PlayerRepository $players,
        private CritterRepository $critters,
        private ClockInterface $clock,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(SignIn $command): User
    {
        $user = $this->users->findByAccountId($command->accountId) ?? $this->linkByEmail($command) ?? $this->join($command);
        if (null !== $command->email) {
            $this->followEmail($user, $command->email);
        }
        $this->transaction->commit();

        return $user;
    }

    private function linkByEmail(SignIn $command): ?User
    {
        if (null === $command->email) {
            return null;
        }

        $user = $this->users->findByEmail(User::normalizeEmail($command->email));
        if (null === $user) {
            return null;
        }
        if (null !== $user->accountId()) {
            throw new EmailAlreadyTaken($user->email());
        }
        $user->linkAccount($command->accountId);

        return $user;
    }

    private function join(SignIn $command): User
    {
        $email = User::normalizeEmail($command->email ?? throw new InvalidEmail(''));
        $user = User::join($command->accountId, $email, $this->displayName($command, $email), $this->timezone($command), $this->clock->now());
        $this->users->add($user);
        $this->players->add(Player::start($user));
        $this->critters->add(Critter::adopt($user, self::CRITTER_NAME, Tint::Sprout));

        return $user;
    }

    private function followEmail(User $user, string $email): void
    {
        $email = User::normalizeEmail($email);
        if ($email === $user->email()) {
            return;
        }

        $holder = $this->users->findByEmail($email);
        if (null !== $holder && $holder !== $user) {
            throw new EmailAlreadyTaken($email);
        }
        $user->changeEmail($email);
    }

    private function displayName(SignIn $command, string $email): string
    {
        $name = trim($command->displayName ?? '');

        return '' === $name ? (string) strstr($email, '@', true) : $name;
    }

    private function timezone(SignIn $command): string
    {
        return \in_array($command->timezone, \DateTimeZone::listIdentifiers(), true) ? (string) $command->timezone : self::DEFAULT_TIMEZONE;
    }
}
