<?php

declare(strict_types=1);

namespace App\Tests\Functional\Web;

use App\Application\Identity\CreateUser\CreateUser;
use App\Application\Identity\CreateUser\CreateUserHandler;
use App\Domain\Gamification\Tint;
use App\Tests\Support\Json;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;

final class AuthenticationTest extends WebTestCase
{
    public function testSignedOutApiCallsAreUnauthorized(): void
    {
        $client = self::createClient();

        $client->jsonRequest('GET', '/api/tasks/today');

        self::assertResponseStatusCodeSame(401);
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
    }

    public function testInvitedUserSetsPasswordThenSignsIn(): void
    {
        $client = self::createClient();
        self::getContainer()->get(CreateUserHandler::class)(new CreateUser('louis@example.com', 'Louis', 'Europe/Paris', 'Pip', Tint::Rust));
        $link = $this->linkFromLastEmail();

        $client->request('GET', $link);
        self::assertResponseRedirects('/password/set');
        $client->request('GET', '/password/set');
        self::assertTrue(Json::at(self::props($client), 'invitation'));

        $client->request('POST', '/password/set', ['_csrf_token' => Json::string(self::props($client), 'csrfToken'), 'password' => 'correct horse battery', 'confirmation' => 'correct horse battery']);
        self::assertResponseRedirects('/login');

        $client->request('GET', '/login');
        $client->request('POST', '/login', ['_csrf_token' => Json::string(self::props($client), 'csrfToken'), 'email' => 'Louis@example.com', 'password' => 'correct horse battery']);
        self::assertResponseRedirects('/');
        $client->jsonRequest('GET', '/api/player');
        self::assertResponseIsSuccessful();
    }

    public function testWrongPasswordIsRejected(): void
    {
        $client = self::createClient();
        self::getContainer()->get(CreateUserHandler::class)(new CreateUser('louis@example.com', 'Louis', 'Europe/Paris', 'Pip', Tint::Rust));

        $client->request('GET', '/login');
        $client->request('POST', '/login', ['_csrf_token' => Json::string(self::props($client), 'csrfToken'), 'email' => 'louis@example.com', 'password' => 'wrong password']);
        $client->followRedirect();

        self::assertSame('Incorrect email or password.', Json::string(self::props($client), 'error'));
    }

    public function testInvalidLinkShowsAnError(): void
    {
        $client = self::createClient();

        $client->request('GET', '/password/set/'.str_repeat('a', 72));
        $client->followRedirect();

        self::assertResponseStatusCodeSame(400);
        self::assertIsString(Json::at(self::props($client), 'linkError'));
    }

    public function testForgotPasswordNeverRevealsAccounts(): void
    {
        $client = self::createClient();

        $client->request('GET', '/password/forgot');
        $client->request('POST', '/password/forgot', ['_csrf_token' => Json::string(self::props($client), 'csrfToken'), 'email' => 'nobody@example.com']);

        self::assertResponseRedirects('/password/forgot');
        self::assertEmailCount(0);
        $client->followRedirect();
        self::assertTrue(Json::at(self::props($client), 'sent'));
    }

    /**
     * @return array<mixed>
     */
    private static function props(KernelBrowser $client): array
    {
        return Json::decode((string) $client->getCrawler()->filter('#auth')->attr('data-props'));
    }

    private function linkFromLastEmail(): string
    {
        $messages = self::getMailerMessages();
        $email = end($messages);
        self::assertInstanceOf(Email::class, $email);
        self::assertSame(1, preg_match('#https?://[^/"]+(/password/set/[0-9a-f]+)#', (string) $email->getHtmlBody(), $matches));

        return $matches[1];
    }
}
