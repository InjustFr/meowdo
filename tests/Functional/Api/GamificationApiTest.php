<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Support\Json;
use App\Tests\Support\SignsInClient;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GamificationApiTest extends WebTestCase
{
    use SignsInClient;

    public function testShowPlayer(): void
    {
        $client = self::signedInClient();

        $client->jsonRequest('GET', '/api/player');

        self::assertResponseIsSuccessful();
        self::assertSame(1, Json::int(self::body($client), 'level'));
        self::assertSame(['name' => 'Mochi', 'coat' => 'ginger', 'mood' => 'sleepy', 'outfit' => ['hat' => null, 'neckwear' => null, 'toy' => null, 'backdrop' => null]], Json::array(self::body($client), 'cat'));
    }

    public function testShopAndOutfit(): void
    {
        $client = self::signedInClient();

        $client->jsonRequest('GET', '/api/shop');
        self::assertResponseIsSuccessful();
        self::assertSame(['slug' => 'party-hat', 'slot' => 'hat', 'price' => 30, 'minLevel' => 1, 'owned' => false, 'worn' => false], Json::array(self::body($client), 0));

        $client->jsonRequest('POST', '/api/shop/party-hat/buy');
        self::assertResponseStatusCodeSame(422);
        $client->jsonRequest('POST', '/api/shop/jetpack/buy');
        self::assertResponseStatusCodeSame(422);
        $client->jsonRequest('POST', '/api/cat/wear/party-hat');
        self::assertResponseStatusCodeSame(422);

        $client->jsonRequest('POST', '/api/cat/take-off/hat');
        self::assertResponseStatusCodeSame(204);
        $client->jsonRequest('POST', '/api/cat/take-off/tail');
        self::assertResponseStatusCodeSame(404);
    }

    public function testRenameAndRecoatTheCat(): void
    {
        $client = self::signedInClient();

        $client->jsonRequest('PUT', '/api/cat/name', ['name' => 'Biscuit']);
        self::assertResponseStatusCodeSame(204);
        $client->jsonRequest('PUT', '/api/cat/coat', ['coat' => 'smoke']);
        self::assertResponseStatusCodeSame(204);

        $client->jsonRequest('GET', '/api/player');
        self::assertSame(['Biscuit', 'smoke'], [Json::string(self::body($client), 'cat', 'name'), Json::string(self::body($client), 'cat', 'coat')]);

        $client->jsonRequest('PUT', '/api/cat/name', ['name' => '']);
        self::assertResponseStatusCodeSame(422);
        $client->jsonRequest('PUT', '/api/cat/name', ['name' => str_repeat('n', 31)]);
        self::assertResponseStatusCodeSame(422);
        $client->jsonRequest('PUT', '/api/cat/coat', ['coat' => 'rainbow']);
        self::assertResponseStatusCodeSame(422);
    }

    public function testAchievementsAreCelebratedOnce(): void
    {
        $client = self::signedInClient();
        $client->jsonRequest('POST', '/api/tasks', ['title' => 'Vet']);
        $client->jsonRequest('POST', '/api/tasks/'.Json::string(self::body($client), 'id').'/complete');

        $client->jsonRequest('GET', '/api/achievements');
        self::assertResponseIsSuccessful();
        self::assertCount(13, self::body($client));
        self::assertSame('first_paw', Json::string(self::body($client), 0, 'id'));
        self::assertIsString(Json::at(self::body($client), 0, 'unlockedAt'));

        $client->jsonRequest('POST', '/api/achievements/seen');
        self::assertResponseStatusCodeSame(204);

        $client->jsonRequest('GET', '/api/player');
        self::assertSame([], Json::array(self::body($client), 'newAchievements'));
    }

    /**
     * @return array<mixed>
     */
    private static function body(KernelBrowser $client): array
    {
        return Json::decode((string) $client->getResponse()->getContent());
    }
}
