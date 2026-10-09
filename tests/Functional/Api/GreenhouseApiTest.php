<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Domain\Gamification\Greenhouse\DewGain;
use App\Domain\Gamification\Greenhouse\GreenhouseRepository;
use App\Domain\Gamification\Herbarium\SpeciesCatalog;
use App\Domain\Gamification\Herbarium\Specimen;
use App\Domain\Identity\User;
use App\Infrastructure\Security\SecurityUser;
use App\Tests\Support\FreezesClock;
use App\Tests\Support\Json;
use App\Tests\Support\SignsInClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Clock;

final class GreenhouseApiTest extends WebTestCase
{
    use FreezesClock;
    use SignsInClient;

    protected function setUp(): void
    {
        self::freezeAt('2026-10-06 08:00 UTC');
    }

    public function testShowGreenhouse(): void
    {
        $client = self::signedInClient();

        $client->jsonRequest('GET', '/api/greenhouse');

        self::assertResponseIsSuccessful();
        $greenhouse = self::body($client);
        self::assertSame(['asOf', 'dew', 'dewGathered', 'tank', 'tankMilli', 'capacity', 'rateMilliPerHour', 'fullAt', 'wateringHours', 'pots', 'maxPots', 'facilities', 'expedition', 'plantable', 'taskDew'], array_keys($greenhouse));
        self::assertSame(['number' => 1, 'species' => null, 'rarity' => null, 'dewPerHour' => 0, 'plantedAt' => null], Json::array($greenhouse, 'pots', 0));
        self::assertSame(['id' => 'glasshouse', 'level' => 1, 'maxLevel' => 11, 'effect' => 2, 'nextEffect' => 3, 'cost' => 80], Json::array($greenhouse, 'facilities', 0));
        self::assertSame(['cost' => 200, 'speciesLeft' => 48], Json::array($greenhouse, 'expedition'));
        self::assertSame(['quadrant' => 'schedule', 'base' => 12, 'watering' => 0, 'mist' => 0, 'amount' => 12], Json::array($greenhouse, 'taskDew', 0));
        self::assertNull($greenhouse['fullAt']);
    }

    public function testPlantDewIsCollectedAndSpent(): void
    {
        [$client, $user] = self::client();
        self::collect($user, 'polytrichum-commune');

        $client->jsonRequest('PUT', '/api/greenhouse/pots/1', ['species' => 'polytrichum-commune']);
        self::assertResponseStatusCodeSame(204);

        $client->jsonRequest('POST', '/api/tasks', ['title' => 'Plan the holidays', 'quadrant' => 'schedule']);
        $client->jsonRequest('POST', '/api/tasks/'.Json::string(self::body($client), 'id').'/complete');
        self::assertSame(['amount' => 16, 'watering' => 4, 'mist' => 0], Json::array(self::body($client), 'dew'));
        self::assertSame([16, false], [Json::int(self::body($client), 'player', 'dew'), Json::at(self::body($client), 'player', 'tankFull')]);

        self::freezeAt('2026-10-06 11:00 UTC');
        $client->jsonRequest('POST', '/api/greenhouse/collect');
        self::assertResponseIsSuccessful();
        self::assertSame(['collected' => 6, 'dew' => 22], self::body($client));

        $client->jsonRequest('POST', '/api/greenhouse/facilities/condenser/upgrade');
        self::assertResponseStatusCodeSame(422);
        self::credit($user, 40);
        $client->jsonRequest('POST', '/api/greenhouse/facilities/condenser/upgrade');
        self::assertResponseStatusCodeSame(204);

        $client->jsonRequest('DELETE', '/api/greenhouse/pots/1');
        self::assertResponseStatusCodeSame(204);

        $client->jsonRequest('GET', '/api/greenhouse');
        $greenhouse = self::body($client);
        self::assertSame([2, 62, 0, 160], [Json::int($greenhouse, 'dew'), Json::int($greenhouse, 'dewGathered'), Json::int($greenhouse, 'tank'), Json::int($greenhouse, 'capacity')]);
        self::assertSame(['species' => 'polytrichum-commune', 'rarity' => 'common', 'dewPerHour' => 2, 'pot' => null], Json::array($greenhouse, 'plantable', 0));
    }

    public function testInvalidPlantingsAre422InEveryLanguage(): void
    {
        [$client, $user] = self::client();
        self::collect($user, 'polytrichum-commune');

        $client->jsonRequest('PUT', '/api/greenhouse/pots/1', ['species' => ' ']);
        self::assertResponseStatusCodeSame(422);

        $client->jsonRequest('PUT', '/api/greenhouse/pots/1', ['species' => 'dandelion']);
        self::assertResponseStatusCodeSame(422);
        self::assertSame('This moss is not in the herbarium.', Json::string(self::body($client), 'detail'));

        $client->jsonRequest('PUT', '/api/greenhouse/pots/9', ['species' => 'polytrichum-commune']);
        self::assertResponseStatusCodeSame(422);
        self::assertSame('There is no pot 9 in your greenhouse yet.', Json::string(self::body($client), 'detail'));

        $client->jsonRequest('PUT', '/api/greenhouse/pots/9', ['species' => 'polytrichum-commune'], ['HTTP_ACCEPT_LANGUAGE' => 'fr']);
        self::assertResponseStatusCodeSame(422);
        self::assertSame('Votre serre n’a pas encore de pot 9.', Json::string(self::body($client), 'detail'));

        $client->jsonRequest('PUT', '/api/greenhouse/pots/0', ['species' => 'polytrichum-commune']);
        self::assertResponseStatusCodeSame(404);
    }

    public function testAnEmptyPotCannotBeEmptied(): void
    {
        $client = self::signedInClient();

        $client->jsonRequest('DELETE', '/api/greenhouse/pots/2');

        self::assertResponseStatusCodeSame(422);
        self::assertSame('Pot 2 is already empty.', Json::string(self::body($client), 'detail'));
    }

    public function testUpgradesNeedDewAndAKnownFacility(): void
    {
        $client = self::signedInClient();

        $client->jsonRequest('POST', '/api/greenhouse/facilities/glasshouse/upgrade', server: ['HTTP_ACCEPT_LANGUAGE' => 'fr']);
        self::assertResponseStatusCodeSame(422);
        self::assertSame('Cela coûte 80 gouttes et vous en avez 0.', Json::string(self::body($client), 'detail'));

        $client->jsonRequest('POST', '/api/greenhouse/facilities/glasshouse/upgrade');
        self::assertSame('This costs 80 dew and you have 0.', Json::string(self::body($client), 'detail'));

        $client->jsonRequest('POST', '/api/greenhouse/facilities/windmill/upgrade');
        self::assertResponseStatusCodeSame(404);
    }

    public function testAnExpeditionBringsBackANewMoss(): void
    {
        [$client, $user] = self::client();
        self::credit($user, 250);

        $client->jsonRequest('POST', '/api/greenhouse/expeditions');

        self::assertResponseStatusCodeSame(201);
        $expedition = self::body($client);
        self::assertSame(['species', 'nextCost'], array_keys($expedition));
        self::assertSame(260, Json::int($expedition, 'nextCost'));
        self::assertSame(SpeciesCatalog::get(Json::string($expedition, 'species', 'slug'))->rarity->value, Json::string($expedition, 'species', 'rarity'));

        $client->jsonRequest('POST', '/api/greenhouse/expeditions');
        self::assertResponseStatusCodeSame(422);
    }

    public function testEachPlayerTendsTheirOwnGreenhouse(): void
    {
        [$client, $user] = self::client();
        self::credit($user, 500);
        $other = self::createUser();
        self::collect($other, 'buxbaumia-aphylla');

        $client->jsonRequest('PUT', '/api/greenhouse/pots/1', ['species' => 'buxbaumia-aphylla']);
        self::assertResponseStatusCodeSame(422);

        $client->loginUser(SecurityUser::fromUser($other));
        $client->jsonRequest('GET', '/api/greenhouse');
        self::assertSame([0, null], [Json::int(self::body($client), 'dew'), Json::at(self::body($client), 'pots', 0, 'species')]);
    }

    /**
     * @return array{KernelBrowser, User}
     */
    private static function client(): array
    {
        $client = self::createClient();
        $user = self::createUser();
        $client->loginUser(SecurityUser::fromUser($user));

        return [$client, $user];
    }

    private static function collect(User $user, string $slug): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $owner = $entityManager->find(User::class, $user->id());
        self::assertNotNull($owner);
        $entityManager->persist(Specimen::collect($owner, SpeciesCatalog::get($slug), Clock::get()->now()));
        $entityManager->flush();
    }

    private static function credit(User $user, int $dew): void
    {
        self::getContainer()->get(GreenhouseRepository::class)->of($user)->receive(new DewGain($dew), Clock::get()->now());
        self::getContainer()->get(EntityManagerInterface::class)->flush();
    }

    /**
     * @return array<mixed>
     */
    private static function body(KernelBrowser $client): array
    {
        return Json::decode((string) $client->getResponse()->getContent());
    }
}
