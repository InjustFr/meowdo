<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261009050345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Greenhouse: versioned against concurrent changes, one pot per species enforced by the database';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE greenhouse ADD version INT DEFAULT 1 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX greenhouse_pot_species ON greenhouse_pot (greenhouse_id, species)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX greenhouse_pot_species');
        $this->addSql('ALTER TABLE greenhouse DROP version');
    }
}
