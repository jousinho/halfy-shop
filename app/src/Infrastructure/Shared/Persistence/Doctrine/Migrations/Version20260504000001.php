<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial MySQL schema — full schema consolidated from all previous PostgreSQL migrations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE IF NOT EXISTS categories (
            id           VARCHAR(36)  NOT NULL,
            name         VARCHAR(100) NOT NULL,
            name_en      VARCHAR(100) DEFAULT NULL,
            slug         VARCHAR(100) NOT NULL,
            sort_order   INT          NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY UNIQ_categories_slug (slug)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE IF NOT EXISTS about_page (
            id             VARCHAR(36)  NOT NULL,
            content        LONGTEXT     NOT NULL,
            content_en     LONGTEXT     DEFAULT NULL,
            photo_filename VARCHAR(255) DEFAULT NULL,
            updated_at     DATETIME     NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE IF NOT EXISTS artworks (
            id             VARCHAR(36)   NOT NULL,
            title          VARCHAR(255)  NOT NULL,
            title_en       VARCHAR(255)  DEFAULT NULL,
            description    LONGTEXT      DEFAULT NULL,
            description_en LONGTEXT      DEFAULT NULL,
            technique      VARCHAR(100)  DEFAULT NULL,
            technique_en   VARCHAR(100)  DEFAULT NULL,
            dimensions     VARCHAR(50)   DEFAULT NULL,
            year           INT           DEFAULT NULL,
            price          DECIMAL(10,2) DEFAULT NULL,
            image_filename VARCHAR(255)  DEFAULT NULL,
            shop_url       VARCHAR(500)  DEFAULT NULL,
            is_available   TINYINT(1)    NOT NULL,
            visible        TINYINT(1)    NOT NULL,
            sort_order     INT           NOT NULL,
            created_at     DATETIME      NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE IF NOT EXISTS artwork_category (
            artwork_id  VARCHAR(36) NOT NULL,
            category_id VARCHAR(36) NOT NULL,
            PRIMARY KEY (artwork_id, category_id),
            INDEX IDX_FA06D53FDB8FFA4  (artwork_id),
            INDEX IDX_FA06D53F12469DE2 (category_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql('ALTER TABLE artwork_category
            ADD CONSTRAINT FK_FA06D53FDB8FFA4  FOREIGN KEY (artwork_id)  REFERENCES artworks   (id) ON DELETE CASCADE,
            ADD CONSTRAINT FK_FA06D53F12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');

        $this->addSql("CREATE TABLE IF NOT EXISTS novedades (
            id            VARCHAR(36)  NOT NULL,
            titulo        VARCHAR(255) NOT NULL,
            titulo_en     VARCHAR(255) DEFAULT NULL,
            contenido     LONGTEXT     DEFAULT NULL,
            contenido_en  LONGTEXT     DEFAULT NULL,
            tipo          VARCHAR(20)  NOT NULL,
            fecha         DATE         NOT NULL    COMMENT '(DC2Type:date_immutable)',
            fecha_fin     DATE         DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
            imagen        VARCHAR(255) DEFAULT NULL,
            lugar         VARCHAR(255) DEFAULT NULL,
            url           VARCHAR(500) DEFAULT NULL,
            video_youtube VARCHAR(500) DEFAULT NULL,
            video_reel    VARCHAR(500) DEFAULT NULL,
            slug          VARCHAR(255) NOT NULL,
            publicado     TINYINT(1)   NOT NULL,
            created_at    DATETIME     NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            PRIMARY KEY (id),
            UNIQUE KEY UNIQ_novedades_slug (slug)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(100) NOT NULL,
            value       LONGTEXT     NOT NULL,
            PRIMARY KEY (setting_key)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("INSERT IGNORE INTO settings (setting_key, value) VALUES ('active_theme', 'default')");

        $this->addSql("CREATE TABLE IF NOT EXISTS sync_logs (
            id          VARCHAR(36) NOT NULL,
            executed_at DATETIME    NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            created     INT         NOT NULL,
            updated     INT         NOT NULL,
            unchanged   INT         NOT NULL,
            log         LONGTEXT    NOT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE artwork_category DROP FOREIGN KEY FK_FA06D53FDB8FFA4');
        $this->addSql('ALTER TABLE artwork_category DROP FOREIGN KEY FK_FA06D53F12469DE2');
        $this->addSql('DROP TABLE IF EXISTS artwork_category');
        $this->addSql('DROP TABLE IF EXISTS artworks');
        $this->addSql('DROP TABLE IF EXISTS categories');
        $this->addSql('DROP TABLE IF EXISTS about_page');
        $this->addSql('DROP TABLE IF EXISTS novedades');
        $this->addSql('DROP TABLE IF EXISTS settings');
        $this->addSql('DROP TABLE IF EXISTS sync_logs');
    }
}
