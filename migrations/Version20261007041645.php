<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261007041645 extends AbstractMigration
{
    private const array TINTS = [
        'ginger' => 'rust',
        'tuxedo' => 'peat',
        'smoke' => 'frost',
        'calico' => 'sprout',
        'midnight' => 'plum',
        'cream' => 'lichen',
    ];

    private const array COSMETICS = [
        'party-hat' => 'acorn-cap',
        'frog-hat' => 'toadstool',
        'crown' => 'flower-crown',
        'bell-collar' => 'leaf-collar',
        'yarn-ball' => 'pebble',
        'fish' => 'dewdrop',
        'mouse' => 'snail',
        'laser-dot' => 'firefly',
        'rooftop' => 'pond',
        'library' => 'terrarium',
        'aquarium' => 'rainfall',
        'sakura' => 'autumn',
    ];

    private const array ACHIEVEMENTS = [
        'first_paw' => 'first_drop',
        'pounce_10' => 'water_10',
        'stalk_25' => 'plant_25',
    ];

    private const array PROJECT_COLORS = [
        'coral' => 'berry',
        'lamp' => 'honey',
        'catnip' => 'moss',
        'sky' => 'fjord',
        'lavender' => 'heather',
        'yarn' => 'blossom',
        'mint' => 'lichen',
        'ginger' => 'rust',
    ];

    private const array OUTFIT_SLOTS = ['hat', 'neckwear', 'toy', 'backdrop'];

    public function getDescription(): string
    {
        return 'MossyDew: the cat becomes a critter with a tint, cosmetics, achievements and project colours are renamed, users may choose a theme';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cat RENAME TO critter');
        $this->addSql('ALTER TABLE critter RENAME COLUMN coat TO tint');
        $this->addSql('ALTER INDEX cat_pkey RENAME TO critter_pkey');
        $this->addSql('ALTER INDEX uniq_9e5e43a87e3c61f9 RENAME TO UNIQ_E4DE48457E3C61F9');
        $this->addSql('ALTER TABLE critter RENAME CONSTRAINT fk_9e5e43a87e3c61f9 TO FK_E4DE48457E3C61F9');
        $this->renameNotNullConstraints('critter', ['cat_coat_not_null' => 'critter_tint_not_null', 'cat_id_not_null' => 'critter_id_not_null', 'cat_name_not_null' => 'critter_name_not_null', 'cat_owner_id_not_null' => 'critter_owner_id_not_null']);
        $this->addSql('ALTER TABLE app_user ADD theme_background VARCHAR(7) DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ADD theme_accent VARCHAR(7) DEFAULT NULL');

        $this->rename(self::TINTS, self::COSMETICS, self::ACHIEVEMENTS, self::PROJECT_COLORS);
    }

    public function down(Schema $schema): void
    {
        $this->rename(array_flip(self::TINTS), array_flip(self::COSMETICS), array_flip(self::ACHIEVEMENTS), array_flip(self::PROJECT_COLORS));

        $this->addSql('ALTER TABLE app_user DROP theme_background');
        $this->addSql('ALTER TABLE app_user DROP theme_accent');
        $this->renameNotNullConstraints('critter', ['critter_tint_not_null' => 'cat_coat_not_null', 'critter_id_not_null' => 'cat_id_not_null', 'critter_name_not_null' => 'cat_name_not_null', 'critter_owner_id_not_null' => 'cat_owner_id_not_null']);
        $this->addSql('ALTER TABLE critter RENAME CONSTRAINT FK_E4DE48457E3C61F9 TO fk_9e5e43a87e3c61f9');
        $this->addSql('ALTER INDEX UNIQ_E4DE48457E3C61F9 RENAME TO uniq_9e5e43a87e3c61f9');
        $this->addSql('ALTER INDEX critter_pkey RENAME TO cat_pkey');
        $this->addSql('ALTER TABLE critter RENAME COLUMN tint TO coat');
        $this->addSql('ALTER TABLE critter RENAME TO cat');
    }

    /**
     * @param array<string, string> $names
     */
    private function renameNotNullConstraints(string $table, array $names): void
    {
        foreach ($names as $from => $to) {
            $this->addSql(\sprintf(
                "DO $$ BEGIN IF EXISTS (SELECT 1 FROM pg_constraint WHERE conrelid = '%1\$s'::regclass AND conname = '%2\$s') THEN ALTER TABLE %1\$s RENAME CONSTRAINT %2\$s TO %3\$s; END IF; END $$",
                $table,
                $from,
                $to,
            ));
        }
    }

    /**
     * @param array<string, string> $tints
     * @param array<string, string> $cosmetics
     * @param array<string, string> $achievements
     * @param array<string, string> $projectColors
     */
    private function rename(array $tints, array $cosmetics, array $achievements, array $projectColors): void
    {
        $this->remap('critter', 'tint', $tints);
        foreach (self::OUTFIT_SLOTS as $slot) {
            $this->remap('critter', $slot, $cosmetics);
        }
        $this->remap('cosmetic_ownership', 'slug', $cosmetics);
        $this->remap('unlocked_achievement', 'achievement', $achievements);
        $this->remap('project', 'color', $projectColors);
    }

    /**
     * @param array<string, string> $mapping
     */
    private function remap(string $table, string $column, array $mapping): void
    {
        $cases = implode(' ', array_fill(0, \count($mapping), 'WHEN ? THEN ?'));
        $placeholders = implode(', ', array_fill(0, \count($mapping), '?'));
        $pairs = [];
        foreach ($mapping as $from => $to) {
            $pairs[] = $from;
            $pairs[] = $to;
        }

        $this->addSql(
            \sprintf('UPDATE %1$s SET %2$s = CASE %2$s %3$s ELSE %2$s END WHERE %2$s IN (%4$s)', $table, $column, $cases, $placeholders),
            [...$pairs, ...array_keys($mapping)],
        );
    }
}
