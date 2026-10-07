<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Gamification;

use App\Application\Gamification\PlayerStatsLedger;
use App\Domain\Gamification\Achievement\PlayerStats;
use App\Domain\Gamification\Player;
use App\Domain\Planning\Quadrant;
use Doctrine\DBAL\Connection;

final readonly class DoctrinePlayerStatsLedger implements PlayerStatsLedger
{
    public function __construct(private Connection $connection)
    {
    }

    public function statsOf(Player $player): PlayerStats
    {
        $owner = $player->owner()->id()->toRfc4122();
        $tasks = $this->connection->fetchAssociative(
            'SELECT
                COUNT(*) FILTER (WHERE rewarded_at IS NOT NULL) AS completed,
                COUNT(*) FILTER (WHERE rewarded_at IS NOT NULL AND quadrant = :doFirst) AS do_first,
                COUNT(*) FILTER (WHERE rewarded_at IS NOT NULL AND quadrant = :schedule) AS schedule,
                COUNT(*) FILTER (WHERE quadrant IS NOT NULL) AS classified
             FROM task WHERE owner_id = :owner',
            ['owner' => $owner, 'doFirst' => Quadrant::DoFirst->value, 'schedule' => Quadrant::Schedule->value],
        );

        return new PlayerStats(
            tasksCompleted: self::int($tasks['completed'] ?? 0),
            doFirstCompleted: self::int($tasks['do_first'] ?? 0),
            scheduleCompleted: self::int($tasks['schedule'] ?? 0),
            tasksClassified: self::int($tasks['classified'] ?? 0),
            bestStreak: $player->streak()->best,
            level: $player->level(),
        );
    }

    private static function int(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
