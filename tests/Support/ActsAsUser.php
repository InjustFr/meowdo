<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Domain\Gamification\Greenhouse\Greenhouse;
use App\Domain\Gamification\Player;
use App\Domain\Identity\User;
use App\Infrastructure\Security\SecurityUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Uid\Ulid;

trait ActsAsUser
{
    protected static function createUser(?string $email = null, string $timezone = 'Europe/Paris'): User
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $id = strtolower((string) new Ulid());
        $user = User::join($id, $email ?? \sprintf('%s@mossydew.test', $id), 'Louis', $timezone, Clock::get()->now());
        $entityManager->persist($user);
        $entityManager->persist(Player::start($user));
        $entityManager->persist(Greenhouse::open($user));
        $entityManager->flush();

        return $user;
    }

    protected static function createUserFromBeforeAccounts(string $email): User
    {
        $user = self::createUser($email);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->getConnection()->executeStatement('UPDATE app_user SET account_id = NULL WHERE id = ?', [$user->id()->toRfc4122()]);
        $entityManager->clear();

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
