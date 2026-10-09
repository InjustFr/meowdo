<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261009043059 extends AbstractMigration
{
    private const array DEW_BY_QUADRANT = [
        'schedule' => 12,
        'do_first' => 6,
        'delegate' => 3,
        'eliminate' => 1,
    ];
    private const int UNSORTED_DEW = 1;
    private const int OPENING_POTS = 2;

    public function getDescription(): string
    {
        return 'Greenhouse: one per player with its pots, opened with the base dew of every task already rewarded';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE greenhouse (id UUID NOT NULL, dew INT NOT NULL, dew_gathered INT NOT NULL, tank_milli INT NOT NULL, settled_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expeditions INT NOT NULL, facilities_glasshouse INT NOT NULL, facilities_condenser INT NOT NULL, facilities_misters INT NOT NULL, facilities_rain_barrel INT NOT NULL, owner_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DC68F11B7E3C61F9 ON greenhouse (owner_id)');
        $this->addSql('CREATE TABLE greenhouse_pot (id UUID NOT NULL, number SMALLINT NOT NULL, species VARCHAR(60) DEFAULT NULL, planted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, greenhouse_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX greenhouse_pot_number ON greenhouse_pot (greenhouse_id, number)');
        $this->addSql('CREATE INDEX IDX_4289DCDC38FCB0EB ON greenhouse_pot (greenhouse_id)');
        $this->addSql('ALTER TABLE greenhouse ADD CONSTRAINT FK_DC68F11B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE greenhouse_pot ADD CONSTRAINT FK_4289DCDC38FCB0EB FOREIGN KEY (greenhouse_id) REFERENCES greenhouse (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->openGreenhousesWithDewAlreadyEarned();
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE greenhouse_pot DROP CONSTRAINT FK_4289DCDC38FCB0EB');
        $this->addSql('ALTER TABLE greenhouse DROP CONSTRAINT FK_DC68F11B7E3C61F9');
        $this->addSql('DROP TABLE greenhouse_pot');
        $this->addSql('DROP TABLE greenhouse');
    }

    private function openGreenhousesWithDewAlreadyEarned(): void
    {
        $dew = 'CASE t.quadrant';
        foreach (self::DEW_BY_QUADRANT as $quadrant => $amount) {
            $dew .= \sprintf(" WHEN '%s' THEN %d", $quadrant, $amount);
        }
        $dew .= \sprintf(' ELSE %d END', self::UNSORTED_DEW);

        $this->addSql(
            'INSERT INTO greenhouse (id, owner_id, dew, dew_gathered, tank_milli, settled_at, expeditions, facilities_glasshouse, facilities_condenser, facilities_misters, facilities_rain_barrel)
            SELECT uuidv7(), p.owner_id, earned.dew, earned.dew, 0, ?, 0, 1, 1, 0, 0
            FROM player p
            CROSS JOIN LATERAL (SELECT COALESCE(SUM('.$dew.'), 0) AS dew FROM task t WHERE t.owner_id = p.owner_id AND t.rewarded_at IS NOT NULL) earned
            WHERE NOT EXISTS (SELECT 1 FROM greenhouse g WHERE g.owner_id = p.owner_id)',
            [new \DateTimeImmutable('now', new \DateTimeZone('UTC'))->format('Y-m-d H:i:s')],
        );
        $this->addSql(
            'INSERT INTO greenhouse_pot (id, greenhouse_id, number)
            SELECT uuidv7(), g.id, number
            FROM greenhouse g
            CROSS JOIN generate_series(1, ?) number
            WHERE NOT EXISTS (SELECT 1 FROM greenhouse_pot pot WHERE pot.greenhouse_id = g.id)',
            [self::OPENING_POTS],
        );
    }
}
