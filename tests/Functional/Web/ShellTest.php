<?php

declare(strict_types=1);

namespace App\Tests\Functional\Web;

use App\Tests\Support\Json;
use App\Tests\Support\SignsInClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ShellTest extends WebTestCase
{
    use SignsInClient;

    public function testSignedInUsersSkipTheLogin(): void
    {
        $client = self::signedInClient();

        $client->request('GET', '/login');

        self::assertResponseRedirects('/');
    }

    public function testSignedOutVisitorsAreSentToLogin(): void
    {
        $client = self::createClient();

        $client->request('GET', '/');

        self::assertResponseRedirects('/login');
    }

    public function testSignedInUsersGetTheShell(): void
    {
        $client = self::signedInClient('louis@mossydew.test');

        $crawler = $client->request('GET', '/today');

        self::assertResponseIsSuccessful();
        $preloaded = Json::decode($crawler->filter('#app-preload')->text());
        self::assertSame(['/api/projects', '/api/player', '/api/tasks/today'], array_keys($preloaded));
        self::assertSame('Pip', Json::string($preloaded, '/api/player', 'critter', 'name'));
        $session = Json::decode($crawler->filter('#app-session')->text());
        self::assertSame(['louis@mossydew.test', 'https://accounts.test'], [Json::string($session, 'email'), Json::string($session, 'accountsUrl')]);
    }
}
