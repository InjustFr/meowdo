<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261006185340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_user (id UUID NOT NULL, email VARCHAR(180) NOT NULL, password_hash VARCHAR(255) DEFAULT NULL, display_name VARCHAR(60) NOT NULL, timezone VARCHAR(64) NOT NULL, language VARCHAR(2) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88BDF3E9E7927C74 ON app_user (email)');
        $this->addSql('CREATE TABLE cat (id UUID NOT NULL, name VARCHAR(30) NOT NULL, coat VARCHAR(16) NOT NULL, hat VARCHAR(40) DEFAULT NULL, neckwear VARCHAR(40) DEFAULT NULL, toy VARCHAR(40) DEFAULT NULL, backdrop VARCHAR(40) DEFAULT NULL, owner_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9E5E43A87E3C61F9 ON cat (owner_id)');
        $this->addSql('CREATE TABLE cosmetic_ownership (id UUID NOT NULL, slug VARCHAR(40) NOT NULL, acquired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, owner_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX cosmetic_ownership_owner_slug ON cosmetic_ownership (owner_id, slug)');
        $this->addSql('CREATE INDEX IDX_2FD7FAA97E3C61F9 ON cosmetic_ownership (owner_id)');
        $this->addSql('CREATE TABLE password_token (id UUID NOT NULL, selector VARCHAR(24) NOT NULL, verifier_hash VARCHAR(64) NOT NULL, purpose VARCHAR(20) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BEAB6C249692E25D ON password_token (selector)');
        $this->addSql('CREATE INDEX IDX_BEAB6C24A76ED395 ON password_token (user_id)');
        $this->addSql('CREATE TABLE player (id UUID NOT NULL, xp INT NOT NULL, coins INT NOT NULL, streak_current INT NOT NULL, streak_best INT NOT NULL, streak_last_active_on DATE DEFAULT NULL, owner_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_98197A657E3C61F9 ON player (owner_id)');
        $this->addSql('CREATE TABLE project (id UUID NOT NULL, name VARCHAR(60) NOT NULL, color VARCHAR(16) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, owner_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX project_owner_name ON project (owner_id, name)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE7E3C61F9 ON project (owner_id)');
        $this->addSql('CREATE TABLE task (id UUID NOT NULL, title VARCHAR(200) NOT NULL, notes TEXT DEFAULT NULL, planned_on DATE DEFAULT NULL, due_on DATE DEFAULT NULL, quadrant VARCHAR(16) DEFAULT NULL, rank INT NOT NULL, completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, rewarded_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, owner_id UUID NOT NULL, project_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX task_owner_planned ON task (owner_id, planned_on)');
        $this->addSql('CREATE INDEX task_owner_quadrant ON task (owner_id, quadrant, rank)');
        $this->addSql('CREATE INDEX IDX_527EDB257E3C61F9 ON task (owner_id)');
        $this->addSql('CREATE INDEX IDX_527EDB25166D1F9C ON task (project_id)');
        $this->addSql('CREATE TABLE unlocked_achievement (id UUID NOT NULL, achievement VARCHAR(40) NOT NULL, unlocked_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, seen_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, owner_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX unlocked_achievement_owner_rule ON unlocked_achievement (owner_id, achievement)');
        $this->addSql('CREATE INDEX IDX_A355C9057E3C61F9 ON unlocked_achievement (owner_id)');
        $this->addSql('CREATE TABLE sessions (sess_id VARCHAR(128) NOT NULL, sess_data BYTEA NOT NULL, sess_lifetime INT NOT NULL, sess_time INT NOT NULL, PRIMARY KEY (sess_id))');
        $this->addSql('CREATE INDEX sess_lifetime_idx ON sessions (sess_lifetime)');
        $this->addSql('ALTER TABLE cat ADD CONSTRAINT FK_9E5E43A87E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE cosmetic_ownership ADD CONSTRAINT FK_2FD7FAA97E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE password_token ADD CONSTRAINT FK_BEAB6C24A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A657E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB257E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE unlocked_achievement ADD CONSTRAINT FK_A355C9057E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cat DROP CONSTRAINT FK_9E5E43A87E3C61F9');
        $this->addSql('ALTER TABLE cosmetic_ownership DROP CONSTRAINT FK_2FD7FAA97E3C61F9');
        $this->addSql('ALTER TABLE password_token DROP CONSTRAINT FK_BEAB6C24A76ED395');
        $this->addSql('ALTER TABLE player DROP CONSTRAINT FK_98197A657E3C61F9');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE7E3C61F9');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB257E3C61F9');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB25166D1F9C');
        $this->addSql('ALTER TABLE unlocked_achievement DROP CONSTRAINT FK_A355C9057E3C61F9');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE cat');
        $this->addSql('DROP TABLE cosmetic_ownership');
        $this->addSql('DROP TABLE password_token');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE unlocked_achievement');
        $this->addSql('DROP TABLE sessions');
    }
}
