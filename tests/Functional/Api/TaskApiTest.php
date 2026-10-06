<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Infrastructure\Security\SecurityUser;
use App\Tests\Support\FreezesClock;
use App\Tests\Support\Json;
use App\Tests\Support\SignsInClient;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TaskApiTest extends WebTestCase
{
    use FreezesClock;
    use SignsInClient;

    protected function setUp(): void
    {
        self::freezeAt('2026-10-06 08:00 UTC');
    }

    public function testTaskLifecycle(): void
    {
        $client = self::signedInClient();

        $client->jsonRequest('POST', '/api/tasks', ['title' => 'Book the vet', 'notes' => 'Booklet', 'plan' => 'tomorrow', 'dueOn' => '2026-10-09', 'quadrant' => 'do_first']);
        self::assertResponseStatusCodeSame(201);
        $task = self::body($client);
        $id = Json::string($task, 'id');
        self::assertSame(['Book the vet', 'Booklet', '2026-10-07', '2026-10-09', 'do_first', false], [$task['title'], $task['notes'], $task['plannedOn'], $task['dueOn'], $task['quadrant'], $task['done']]);

        $client->jsonRequest('PATCH', "/api/tasks/$id", ['title' => 'Book the vet today', 'dueOn' => null]);
        self::assertResponseIsSuccessful();
        self::assertSame(['Book the vet today', null, null], [Json::at(self::body($client), 'title'), Json::at(self::body($client), 'notes'), Json::at(self::body($client), 'dueOn')]);

        $client->jsonRequest('POST', "/api/tasks/$id/plan", ['when' => 'date', 'date' => '2026-10-20']);
        self::assertResponseIsSuccessful();
        self::assertSame('2026-10-20', Json::string(self::body($client), 'plannedOn'));

        $client->jsonRequest('POST', "/api/tasks/$id/classify", ['quadrant' => 'schedule']);
        self::assertResponseIsSuccessful();
        self::assertSame('schedule', Json::string(self::body($client), 'quadrant'));

        $client->jsonRequest('POST', "/api/tasks/$id/complete");
        self::assertResponseIsSuccessful();
        $completion = self::body($client);
        self::assertTrue(Json::at($completion, 'task', 'done'));
        self::assertSame(['xp' => 26, 'coins' => 7], Json::array($completion, 'reward'));
        self::assertSame(26, Json::int($completion, 'player', 'xp'));
        self::assertSame(['first_paw'], Json::array($completion, 'player', 'newAchievements'));

        $client->jsonRequest('POST', "/api/tasks/$id/reopen");
        self::assertResponseIsSuccessful();
        self::assertFalse(Json::at(self::body($client), 'done'));

        $client->jsonRequest('DELETE', "/api/tasks/$id");
        self::assertResponseStatusCodeSame(204);

        $client->jsonRequest('GET', '/api/matrix');
        self::assertSame([], self::body($client));
    }

    public function testInvalidPayloadsAre422(): void
    {
        $client = self::signedInClient();

        foreach ([['title' => '  '], ['title' => str_repeat('a', 201)], ['title' => 'Vet', 'dueOn' => '2026-02-30'], ['title' => 'Vet', 'plan' => 'date'], ['title' => 'Vet', 'plan' => 'someday'], ['title' => 'Vet', 'quadrant' => 'urgent']] as $payload) {
            $client->jsonRequest('POST', '/api/tasks', $payload);
            self::assertResponseStatusCodeSame(422, (string) json_encode($payload));
        }
    }

    public function testCompletingTwiceIs422(): void
    {
        $client = self::signedInClient();
        $id = self::create($client, 'Vet');
        $client->jsonRequest('POST', "/api/tasks/$id/complete");

        $client->jsonRequest('POST', "/api/tasks/$id/complete");

        self::assertResponseStatusCodeSame(422);
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');
        self::assertSame(422, Json::int(self::body($client), 'status'));
    }

    public function testUnknownTaskIs404(): void
    {
        $client = self::signedInClient();

        $client->jsonRequest('POST', '/api/tasks/01K00000000000000000000000/complete');

        self::assertResponseStatusCodeSame(404);
    }

    public function testAnotherUsersTaskIs404(): void
    {
        $client = self::signedInClient();
        $id = self::create($client, 'Secret');
        $client->loginUser(SecurityUser::fromUser(self::createUser()));

        foreach ([['PATCH', "/api/tasks/$id", ['title' => 'Mine']], ['POST', "/api/tasks/$id/complete", []], ['POST', "/api/tasks/$id/plan", ['when' => 'today']], ['DELETE', "/api/tasks/$id", []]] as [$method, $url, $payload]) {
            $client->jsonRequest($method, $url, $payload);
            self::assertResponseStatusCodeSame(404, "$method $url");
        }
    }

    public function testListsAnswer200(): void
    {
        $client = self::signedInClient();
        self::create($client, 'Thought', ['plan' => 'tomorrow']);

        $client->jsonRequest('GET', '/api/tasks/today');
        self::assertResponseIsSuccessful();
        self::assertSame('2026-10-06', Json::string(self::body($client), 'date'));

        $client->jsonRequest('GET', '/api/tasks/upcoming?from=2026-10-07');
        self::assertResponseIsSuccessful();
        self::assertSame('Thought', Json::string(self::body($client), 'tasks', 0, 'title'));

        $client->jsonRequest('GET', '/api/tasks/upcoming?from=soon');
        self::assertResponseStatusCodeSame(422);

        $client->jsonRequest('GET', '/api/tasks/inbox');
        self::assertResponseIsSuccessful();
        self::assertCount(1, self::body($client));

        $client->jsonRequest('GET', '/api/matrix');
        self::assertResponseIsSuccessful();
        self::assertCount(1, self::body($client));
    }

    public function testATaskPlannedTodayInParisIsListedInTodayNotEarlier(): void
    {
        self::freezeAt('2026-10-06 22:30 UTC');
        $client = self::signedInClient();
        self::create($client, 'Planned today', ['plan' => 'today']);
        self::create($client, 'Planned yesterday', ['plan' => 'date', 'planDate' => '2026-10-06']);

        $client->jsonRequest('GET', '/api/tasks/today');

        self::assertSame('2026-10-07', Json::string(self::body($client), 'date'));
        self::assertSame(['Planned today'], array_column(Json::array(self::body($client), 'today'), 'title'));
        self::assertSame(['Planned yesterday'], array_column(Json::array(self::body($client), 'earlier'), 'title'));
    }

    public function testMoveOverdueToToday(): void
    {
        $client = self::signedInClient();
        self::create($client, 'Last week', ['plan' => 'date', 'planDate' => '2026-09-29']);

        $client->jsonRequest('POST', '/api/tasks/overdue/plan-today');

        self::assertResponseIsSuccessful();
        self::assertSame(['moved' => 1], self::body($client));
    }

    public function testReorderQuadrant(): void
    {
        $client = self::signedInClient();
        $first = self::create($client, 'First', ['quadrant' => 'do_first']);
        $second = self::create($client, 'Second', ['quadrant' => 'do_first']);

        $client->jsonRequest('PUT', '/api/matrix/do_first/order', ['ids' => [$second, $first]]);
        self::assertResponseStatusCodeSame(204);

        $client->jsonRequest('GET', '/api/matrix');
        self::assertSame(['Second', 'First'], array_column(self::body($client), 'title'));

        $client->jsonRequest('PUT', '/api/matrix/do_first/order', ['ids' => ['not-a-ulid']]);
        self::assertResponseStatusCodeSame(422);

        $client->jsonRequest('PUT', '/api/matrix/urgent/order', ['ids' => []]);
        self::assertResponseStatusCodeSame(404);
    }

    public function testWritesCanAskForRefreshedLists(): void
    {
        $client = self::signedInClient();

        $client->jsonRequest('POST', '/api/tasks', ['title' => 'Vet'], ['HTTP_X_REFRESH' => json_encode(['/api/tasks/inbox', 'https://evil.example/'])]);

        self::assertResponseStatusCodeSame(200);
        self::assertResponseHeaderSame('X-Refreshed', '1');
        $body = self::body($client);
        self::assertSame('Vet', Json::string($body, 'data', 'title'));
        self::assertSame(['/api/tasks/inbox'], array_keys(Json::array($body, 'refreshed')));
        self::assertSame('Vet', Json::string($body, 'refreshed', '/api/tasks/inbox', 0, 'title'));
    }

    /**
     * @param array<string, string> $payload
     */
    private static function create(KernelBrowser $client, string $title, array $payload = []): string
    {
        $client->jsonRequest('POST', '/api/tasks', ['title' => $title, ...$payload]);
        self::assertResponseStatusCodeSame(201, (string) $client->getResponse()->getContent());

        return Json::string(self::body($client), 'id');
    }

    /**
     * @return array<mixed>
     */
    private static function body(KernelBrowser $client): array
    {
        return Json::decode((string) $client->getResponse()->getContent());
    }
}
