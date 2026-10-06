<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Domain\Gamification\Cat;
use App\Domain\Gamification\Coat;
use App\Domain\Gamification\Player;
use App\Domain\Identity\User;
use App\Infrastructure\Security\SecurityUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Uid\Ulid;

trait ActsAsUser
{
    protected static function createUser(?string $email = null, string $timezone = 'Europe/Paris', string $catName = 'Mochi'): User
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $user = User::invite($email ?? \sprintf('%s@meowdo.test', strtolower((string) new Ulid())), 'Louis', $timezone, Clock::get()->now());
        $user->changePassword('not-a-real-hash');
        $entityManager->persist($user);
        $entityManager->persist(Player::start($user));
        $entityManager->persist(Cat::adopt($user, $catName, Coat::Ginger));
        $entityManager->flush();

        return $user;
    }

    protected static function actAs(User $user): void
    {
        $securityUser = SecurityUser::fromUser($user);
        self::getContainer()->get('security.token_storage')->setToken(new UsernamePasswordToken($securityUser, 'main', $securityUser->getRoles()));
    }

    protected static function actAsNewUser(string $timezone = 'Europe/Paris'): User
    {
        $user = self::createUser(timezone: $timezone);
        self::actAs($user);

        return $user;
    }
}
