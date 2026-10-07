<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261007051241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Index task completions per owner for the done page and statistics';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX task_owner_completed ON task (owner_id, completed_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX task_owner_completed');
    }
}
