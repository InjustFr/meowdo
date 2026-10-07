<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261007045933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Recurring tasks';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE task ADD recurrence_unit VARCHAR(8) DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD recurrence_interval SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE task DROP recurrence_unit');
        $this->addSql('ALTER TABLE task DROP recurrence_interval');
    }
}
