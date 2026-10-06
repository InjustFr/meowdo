<?php

declare(strict_types=1);

namespace App\Application\Identity\ResendInvitation;

use App\Application\Identity\AccountMailer;
use App\Application\Identity\PasswordTokenIssuer;
use App\Application\Transaction;
use App\Domain\Identity\Exception\AccountAlreadyActive;
use App\Domain\Identity\Exception\UnknownAccount;
use App\Domain\Identity\PasswordTokenPurpose;
use App\Domain\Identity\User;
use App\Domain\Identity\UserRepository;

final readonly class ResendInvitationHandler
{
    public function __construct(
        private UserRepository $users,
        private PasswordTokenIssuer $tokens,
        private AccountMailer $mailer,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(string $email): User
    {
        $user = $this->users->findByEmail($email) ?? throw new UnknownAccount(trim($email));
        if (null !== $user->passwordHash()) {
            throw new AccountAlreadyActive($user->email());
        }

        [$passwordToken, $token] = $this->tokens->issue($user, PasswordTokenPurpose::Invitation);
        $this->transaction->commit();

        $this->mailer->sendInvitation($passwordToken, $token);

        return $user;
    }
}
