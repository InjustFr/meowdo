<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Ulid;

final class Version20261007173514 extends AbstractMigration
{
    private const int XP_PER_LEVEL = 150;

    private const array SPECIES = [
        'polytrichum-commune',
        'polytrichum-juniperinum',
        'sphagnum-palustre',
        'sphagnum-capillifolium',
        'hylocomium-splendens',
        'pleurozium-schreberi',
        'hypnum-cupressiforme',
        'dicranum-scoparium',
        'leucobryum-glaucum',
        'thuidium-tamariscinum',
        'ceratodon-purpureus',
        'funaria-hygrometrica',
        'bryum-argenteum',
        'tortula-muralis',
        'grimmia-pulvinata',
        'syntrichia-ruralis',
        'kindbergia-praelonga',
        'brachythecium-rutabulum',
        'rhytidiadelphus-squarrosus',
        'rhytidiadelphus-loreus',
        'atrichum-undulatum',
        'mnium-hornum',
        'plagiomnium-undulatum',
        'plagiomnium-affine',
        'fissidens-taxifolius',
        'climacium-dendroides',
        'fontinalis-antipyretica',
        'homalothecium-sericeum',
        'pseudoscleropodium-purum',
        'leucodon-sciuroides',
        'ulota-crispa',
        'tetraphis-pellucida',
        'buxbaumia-aphylla',
        'splachnum-luteum',
        'schistostega-pennata',
        'hookeria-lucens',
        'calliergonella-cuspidata',
        'racomitrium-lanuginosum',
        'physcomitrium-pyriforme',
        'aulacomnium-palustre',
        'dicranella-heteromalla',
        'pogonatum-urnigerum',
        'ptilium-crista-castrensis',
        'andreaea-rupestris',
        'rhizomnium-punctatum',
        'bartramia-pomiformis',
        'encalypta-streptocarpa',
        'hedwigia-ciliata',
    ];

    public function getDescription(): string
    {
        return 'Herbarium replaces the critter: specimens table with one random species per level already reached, critter, cosmetics, coins and the first purchase achievement removed';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE specimen (id UUID NOT NULL, species VARCHAR(60) NOT NULL, collected_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, owner_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX specimen_owner_species ON specimen (owner_id, species)');
        $this->addSql('CREATE INDEX IDX_999F39027E3C61F9 ON specimen (owner_id)');
        $this->addSql('ALTER TABLE specimen ADD CONSTRAINT FK_999F39027E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->collectSpeciesForLevelsReached();
        $this->addSql('ALTER TABLE cosmetic_ownership DROP CONSTRAINT fk_2fd7faa97e3c61f9');
        $this->addSql('ALTER TABLE critter DROP CONSTRAINT fk_e4de48457e3c61f9');
        $this->addSql('DROP TABLE cosmetic_ownership');
        $this->addSql('DROP TABLE critter');
        $this->addSql('ALTER TABLE player DROP coins');
        $this->addSql("DELETE FROM unlocked_achievement WHERE achievement = 'first_purchase'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cosmetic_ownership (id UUID NOT NULL, slug VARCHAR(40) NOT NULL, acquired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, owner_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_2fd7faa97e3c61f9 ON cosmetic_ownership (owner_id)');
        $this->addSql('CREATE UNIQUE INDEX cosmetic_ownership_owner_slug ON cosmetic_ownership (owner_id, slug)');
        $this->addSql('CREATE TABLE critter (id UUID NOT NULL, name VARCHAR(30) NOT NULL, tint VARCHAR(16) NOT NULL, hat VARCHAR(40) DEFAULT NULL, neckwear VARCHAR(40) DEFAULT NULL, toy VARCHAR(40) DEFAULT NULL, backdrop VARCHAR(40) DEFAULT NULL, owner_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_e4de48457e3c61f9 ON critter (owner_id)');
        $this->addSql('ALTER TABLE cosmetic_ownership ADD CONSTRAINT fk_2fd7faa97e3c61f9 FOREIGN KEY (owner_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE critter ADD CONSTRAINT fk_e4de48457e3c61f9 FOREIGN KEY (owner_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql("INSERT INTO critter (id, name, tint, owner_id) SELECT gen_random_uuid(), 'Pip', 'sprout', id FROM app_user");
        $this->addSql('ALTER TABLE specimen DROP CONSTRAINT FK_999F39027E3C61F9');
        $this->addSql('DROP TABLE specimen');
        $this->addSql('ALTER TABLE player ADD coins INT DEFAULT 0 NOT NULL');
    }

    private function collectSpeciesForLevelsReached(): void
    {
        $now = new \DateTimeImmutable()->format('Y-m-d H:i:s');
        foreach ($this->connection->fetchAllAssociative('SELECT owner_id, xp FROM player') as $player) {
            $species = self::SPECIES;
            shuffle($species);
            foreach (\array_slice($species, 0, self::levelFor((int) $player['xp']) - 1) as $slug) {
                $this->addSql(
                    'INSERT INTO specimen (id, owner_id, species, collected_at) VALUES (?, ?, ?, ?)',
                    [new Ulid()->toRfc4122(), $player['owner_id'], $slug, $now],
                );
            }
        }
    }

    private static function levelFor(int $xp): int
    {
        return intdiv(max(0, $xp), self::XP_PER_LEVEL) + 1;
    }
}
