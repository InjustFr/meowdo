<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\Identity\ResendInvitation\ResendInvitationHandler;
use App\Domain\Shared\Exception\DomainException;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:user:invite', description: 'Sends a new invitation link to a user who has not chosen a password yet')]
final readonly class ResendInvitationCommand
{
    public function __construct(private ResendInvitationHandler $resendInvitation, private TranslatorInterface $translator)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument(description: 'Email address of the account')]
        string $email,
    ): int {
        try {
            $user = ($this->resendInvitation)($email);
        } catch (DomainException $exception) {
            $io->error($this->translator->trans($exception->getMessage(), $exception->parameters(), 'exceptions'));

            return Command::FAILURE;
        }

        $io->success(\sprintf('New invitation sent to %s; earlier links no longer work.', $user->email()));

        return Command::SUCCESS;
    }
}
