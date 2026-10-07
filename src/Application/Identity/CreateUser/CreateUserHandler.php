<?php

declare(strict_types=1);

namespace App\Application\Identity\CreateUser;

use App\Application\Identity\AccountMailer;
use App\Application\Identity\PasswordTokenIssuer;
use App\Application\Transaction;
use App\Domain\Gamification\Critter;
use App\Domain\Gamification\CritterRepository;
use App\Domain\Gamification\Player;
use App\Domain\Gamification\PlayerRepository;
use App\Domain\Identity\Exception\EmailAlreadyTaken;
use App\Domain\Identity\PasswordTokenPurpose;
use App\Domain\Identity\User;
use App\Domain\Identity\UserRepository;

final readonly class CreateUserHandler
{
    public function __construct(
        private UserRepository $users,
        private PlayerRepository $players,
        private CritterRepository $critters,
        private PasswordTokenIssuer $tokens,
        private AccountMailer $mailer,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(CreateUser $command): User
    {
        $email = User::normalizeEmail($command->email);
        if (null !== $this->users->findByEmail($email)) {
            throw new EmailAlreadyTaken($email);
        }

        $user = User::invite($email, $command->displayName, $command->timezone, $this->tokens->now());
        $this->users->add($user);
        $this->players->add(Player::start($user));
        $this->critters->add(Critter::adopt($user, $command->critterName, $command->tint));
        [$passwordToken, $token] = $this->tokens->issue($user, PasswordTokenPurpose::Invitation);
        $this->transaction->commit();

        $this->mailer->sendInvitation($passwordToken, $token);

        return $user;
    }
}
