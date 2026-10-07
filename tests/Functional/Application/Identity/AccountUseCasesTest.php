<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Identity;

use App\Application\Identity\ChangeTimezone\ChangeTimezoneHandler;
use App\Application\Identity\ChooseLanguage\ChooseLanguageHandler;
use App\Application\Identity\CreateUser\CreateUser;
use App\Application\Identity\CreateUser\CreateUserHandler;
use App\Application\Identity\RequestPasswordReset\RequestPasswordResetHandler;
use App\Application\Identity\ResendInvitation\ResendInvitationHandler;
use App\Application\Identity\SetPassword\SetPasswordHandler;
use App\Domain\Gamification\CritterRepository;
use App\Domain\Gamification\PlayerRepository;
use App\Domain\Gamification\Tint;
use App\Domain\Identity\Exception\AccountAlreadyActive;
use App\Domain\Identity\Exception\EmailAlreadyTaken;
use App\Domain\Identity\Exception\PasswordTokenExpired;
use App\Domain\Identity\Exception\PasswordTooShort;
use App\Domain\Identity\Exception\UnknownAccount;
use App\Domain\Identity\Exception\UnknownPasswordToken;
use App\Domain\Identity\Exception\UnknownTimezone;
use App\Domain\Identity\Language;
use App\Domain\Identity\User;
use App\Tests\Support\ActsAsUser;
use App\Tests\Support\FreezesClock;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class AccountUseCasesTest extends KernelTestCase
{
    use ActsAsUser;
    use FreezesClock;
    use MailerAssertionsTrait;

    protected function setUp(): void
    {
        self::freezeAt('2026-10-06 08:00 UTC');
    }

    public function testCreatingAUserAdoptsTheirCatAndSendsAnInvitation(): void
    {
        $user = $this->createAccount('Louis@Example.com');

        self::assertSame(['louis@example.com', 'Louis', 'Europe/Paris', null], [$user->email(), $user->displayName(), $user->timezone(), $user->passwordHash()]);
        $player = self::getContainer()->get(PlayerRepository::class)->of($user);
        self::assertSame([0, 0, 1], [$player->xp(), $player->coins(), $player->level()]);
        $critter = self::getContainer()->get(CritterRepository::class)->of($user);
        self::assertSame(['Pip', Tint::Sprout], [$critter->name(), $critter->tint()]);
        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertEmailAddressContains($email, 'To', 'louis@example.com');
        self::assertMatchesRegularExpression('#/password/set/[0-9a-f]{72}#', $this->linkFrom($email));
    }

    public function testEmailsAreUnique(): void
    {
        $this->createAccount('louis@example.com');

        $this->expectExceptionObject(new EmailAlreadyTaken('louis@example.com'));

        $this->createAccount(' LOUIS@example.com');
    }

    public function testTimezoneMustExist(): void
    {
        $this->expectExceptionObject(new UnknownTimezone('Europe/Atlantis'));

        self::getContainer()->get(CreateUserHandler::class)(new CreateUser('louis@example.com', 'Louis', 'Europe/Atlantis', 'Pip', Tint::Rust));
    }

    public function testTheInvitationSetsThePasswordOnce(): void
    {
        $this->createAccount('louis@example.com');
        $token = $this->tokenFromLastEmail();

        $user = self::getContainer()->get(SetPasswordHandler::class)($token, 'correct horse battery');

        $hasher = self::getContainer()->get(PasswordHasherFactoryInterface::class)->getPasswordHasher(PasswordAuthenticatedUserInterface::class);
        self::assertTrue($hasher->verify((string) $user->passwordHash(), 'correct horse battery'));

        $this->expectExceptionObject(new UnknownPasswordToken());
        self::getContainer()->get(SetPasswordHandler::class)($token, 'another long password');
    }

    public function testPasswordNeedsTwelveCharacters(): void
    {
        $this->createAccount('louis@example.com');

        $this->expectExceptionObject(new PasswordTooShort(12));

        self::getContainer()->get(SetPasswordHandler::class)($this->tokenFromLastEmail(), 'short pass');
    }

    public function testTheInvitationExpiresAfterSevenDays(): void
    {
        $this->createAccount('louis@example.com');
        self::freezeAt('2026-10-13 08:00 UTC');

        $this->expectExceptionObject(new PasswordTokenExpired());

        self::getContainer()->get(SetPasswordHandler::class)($this->tokenFromLastEmail(), 'correct horse battery');
    }

    public function testPasswordResetIsSentOnlyToKnownEmails(): void
    {
        $this->createAccount('louis@example.com');

        self::getContainer()->get(RequestPasswordResetHandler::class)('nobody@example.com');
        self::assertEmailCount(1);

        self::getContainer()->get(RequestPasswordResetHandler::class)('louis@example.com');
        self::assertEmailCount(2);
    }

    public function testThePasswordResetLinkLastsOneHour(): void
    {
        $this->createAccount('louis@example.com');
        self::getContainer()->get(RequestPasswordResetHandler::class)('louis@example.com');
        self::freezeAt('2026-10-06 09:00 UTC');

        $this->expectExceptionObject(new PasswordTokenExpired());

        self::getContainer()->get(SetPasswordHandler::class)($this->tokenFromLastEmail(), 'correct horse battery');
    }

    public function testChangeTimezoneAndLanguage(): void
    {
        $user = self::actAsNewUser();

        self::getContainer()->get(ChangeTimezoneHandler::class)('Asia/Tokyo');
        self::getContainer()->get(ChooseLanguageHandler::class)(Language::French);

        self::assertSame(['Asia/Tokyo', Language::French], [$user->timezone(), $user->language()]);
    }

    public function testResendingAnInvitationReplacesTheEarlierLink(): void
    {
        $this->createAccount('louis@example.com');
        $firstToken = $this->tokenFromLastEmail();

        self::getContainer()->get(ResendInvitationHandler::class)('Louis@Example.com');

        self::assertEmailCount(2);
        $secondToken = $this->tokenFromLastEmail();
        self::assertNotSame($firstToken, $secondToken);
        $this->expectException(UnknownPasswordToken::class);
        self::getContainer()->get(SetPasswordHandler::class)($firstToken, 'a long enough password');
    }

    public function testAnInvitationCannotBeResentToAnUnknownEmail(): void
    {
        $this->expectException(UnknownAccount::class);

        self::getContainer()->get(ResendInvitationHandler::class)('nobody@example.com');
    }

    public function testAnInvitationCannotBeResentOnceThePasswordIsChosen(): void
    {
        $this->createAccount('louis@example.com');
        self::getContainer()->get(SetPasswordHandler::class)($this->tokenFromLastEmail(), 'a long enough password');

        $this->expectException(AccountAlreadyActive::class);
        self::getContainer()->get(ResendInvitationHandler::class)('louis@example.com');
    }

    private function createAccount(string $email): User
    {
        return self::getContainer()->get(CreateUserHandler::class)(new CreateUser($email, 'Louis', 'Europe/Paris', 'Pip', Tint::Sprout));
    }

    private function tokenFromLastEmail(): string
    {
        $messages = self::getMailerMessages();
        $email = end($messages);
        self::assertInstanceOf(Email::class, $email);
        self::assertSame(1, preg_match('#/password/set/([0-9a-f]+)#', $this->linkFrom($email), $matches));

        return $matches[1];
    }

    private function linkFrom(Email $email): string
    {
        self::assertSame(1, preg_match('#https?://[^"\s]+/password/set/[0-9a-f]+#', (string) $email->getHtmlBody(), $matches));

        return $matches[0];
    }
}
