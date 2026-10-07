<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\Identity\CreateUser\CreateUser;
use App\Application\Identity\CreateUser\CreateUserHandler;
use App\Domain\Gamification\Tint;
use App\Domain\Shared\Exception\DomainException;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:user:create', description: 'Creates a user with their critter and emails them a link to choose their password')]
final readonly class CreateUserCommand
{
    public function __construct(private CreateUserHandler $createUser, private TranslatorInterface $translator)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument(description: 'Email address (login)')]
        string $email,
        #[Option(description: 'Name shown in the app (defaults to the part of the email before @)')]
        string $name = '',
        #[Option(description: 'IANA time zone that decides when "today" starts')]
        string $timezone = 'Europe/Paris',
        #[Option(description: 'Name of their moss piglet companion')]
        string $critter = 'Pip',
        #[Option(description: 'Tint of their critter: sprout, lichen, peat, rust, frost or plum')]
        string $tint = 'sprout',
    ): int {
        $chosenTint = Tint::tryFrom($tint);
        if (null === $chosenTint) {
            $io->error(\sprintf('Unknown tint "%s".', $tint));

            return Command::INVALID;
        }

        try {
            $user = ($this->createUser)(new CreateUser($email, '' === trim($name) ? strstr($email, '@', true) ?: $email : $name, $timezone, $critter, $chosenTint));
        } catch (DomainException $exception) {
            $io->error($this->translator->trans($exception->getMessage(), $exception->parameters(), 'exceptions'));

            return Command::FAILURE;
        }

        $io->success(\sprintf('User %s created with their critter "%s"; invitation sent.', $user->email(), $critter));

        return Command::SUCCESS;
    }
}
