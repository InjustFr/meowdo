<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261007161132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Subtasks: a task may belong to a parent task, deleted with it';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE task ADD parent_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25727ACA70 FOREIGN KEY (parent_id) REFERENCES task (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_527EDB25727ACA70 ON task (parent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB25727ACA70');
        $this->addSql('DROP INDEX IDX_527EDB25727ACA70');
        $this->addSql('ALTER TABLE task DROP parent_id');
    }
}
