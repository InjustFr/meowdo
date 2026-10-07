<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Support\FreezesClock;
use App\Tests\Support\Json;
use App\Tests\Support\SignsInClient;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HistoryApiTest extends WebTestCase
{
    use FreezesClock;
    use SignsInClient;

    protected function setUp(): void
    {
        self::freezeAt('2026-10-07 08:00 UTC');
    }

    public function testDoneTasksAreGroupedByDay(): void
    {
        $client = self::signedInClient();
        $client->jsonRequest('POST', '/api/tasks', ['title' => 'Water the moss']);
        $id = Json::string(self::body($client), 'id');
        $client->jsonRequest('POST', "/api/tasks/$id/complete");

        $client->jsonRequest('GET', '/api/tasks/done');

        self::assertResponseIsSuccessful();
        self::assertSame('2026-10-07', Json::string(self::body($client), 'days', 0, 'date'));
        self::assertSame('Water the moss', Json::string(self::body($client), 'days', 0, 'tasks', 0, 'title'));
        self::assertNull(Json::at(self::body($client), 'older'));

        $client->jsonRequest('GET', '/api/tasks/done?before=2026-10-07');
        self::assertResponseIsSuccessful();
        self::assertSame(['days' => [], 'older' => null], self::body($client));

        $client->jsonRequest('GET', '/api/tasks/done?before=yesterday');
        self::assertResponseStatusCodeSame(422);
    }

    public function testStatistics(): void
    {
        $client = self::signedInClient();
        $client->jsonRequest('POST', '/api/tasks', ['title' => 'Water the moss', 'quadrant' => 'schedule']);
        $id = Json::string(self::body($client), 'id');
        $client->jsonRequest('POST', "/api/tasks/$id/complete");
        $client->jsonRequest('POST', '/api/tasks', ['title' => 'Prune', 'dueOn' => '2026-10-01']);

        $client->jsonRequest('GET', '/api/stats');

        self::assertResponseIsSuccessful();
        $statistics = self::body($client);
        self::assertSame(['completed' => 1, 'completedThisWeek' => 1, 'completedThisMonth' => 1, 'open' => 1, 'overdue' => 1], Json::array($statistics, 'totals'));
        self::assertSame([1, 1], [Json::int($statistics, 'streak'), Json::int($statistics, 'bestStreak')]);
        self::assertSame(['date' => '2026-10-07', 'count' => 1], Json::array($statistics, 'perDay', 29));
        self::assertSame(1, Json::int($statistics, 'byQuadrant', 'schedule'));
        self::assertSame([['id' => null, 'name' => null, 'color' => null, 'count' => 1]], Json::array($statistics, 'byProject'));
        self::assertSame(['onTime' => 0, 'late' => 0], Json::array($statistics, 'onTime'));
    }

    public function testTheShellPreloadsBothPages(): void
    {
        $client = self::signedInClient();

        foreach (['/done' => '/api/tasks/done', '/stats' => '/api/stats'] as $page => $url) {
            $crawler = $client->request('GET', $page);
            self::assertResponseIsSuccessful();
            self::assertArrayHasKey($url, Json::decode($crawler->filter('#app-preload')->text()), $page);
        }
    }

    public function testAnonymousRequestsAreRejected(): void
    {
        $client = self::createClient();

        foreach (['/api/tasks/done', '/api/stats'] as $url) {
            $client->jsonRequest('GET', $url);
            self::assertResponseStatusCodeSame(401, $url);
        }
    }

    /**
     * @return array<mixed>
     */
    private static function body(KernelBrowser $client): array
    {
        return Json::decode((string) $client->getResponse()->getContent());
    }
}
