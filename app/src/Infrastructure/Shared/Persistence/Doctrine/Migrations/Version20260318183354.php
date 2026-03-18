<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260318183354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Drop old tables if they exist (only present in dev DB from previous project)
        $this->addSql('DROP TABLE IF EXISTS illustration_categories');
        $this->addSql('DROP TABLE IF EXISTS illustrations');

        // Migrate categories.id from UUID to VARCHAR(36) if categories table exists
        $this->addSql("DO $$ BEGIN IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'categories') THEN ALTER TABLE categories ALTER id TYPE VARCHAR(36); END IF; END $$");

        // Create new tables
        $this->addSql('CREATE TABLE IF NOT EXISTS categories (id VARCHAR(36) NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, sort_order INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_3AF34668989D9B62 ON categories (slug)');
        $this->addSql('CREATE TABLE about_page (id VARCHAR(36) NOT NULL, content TEXT NOT NULL, photo_filename VARCHAR(255) DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE artworks (id VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, technique VARCHAR(100) NOT NULL, dimensions VARCHAR(50) NOT NULL, year INT NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, image_filename VARCHAR(255) NOT NULL, shop_url VARCHAR(500) DEFAULT NULL, is_available BOOLEAN NOT NULL, sort_order INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE artwork_category (artwork_id VARCHAR(36) NOT NULL, category_id VARCHAR(36) NOT NULL, PRIMARY KEY (artwork_id, category_id))');
        $this->addSql('CREATE INDEX IDX_FA06D53FDB8FFA4 ON artwork_category (artwork_id)');
        $this->addSql('CREATE INDEX IDX_FA06D53F12469DE2 ON artwork_category (category_id)');
        $this->addSql('CREATE TABLE artwork_tag (artwork_id VARCHAR(36) NOT NULL, tag_id VARCHAR(36) NOT NULL, PRIMARY KEY (artwork_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_B9EB001EDB8FFA4 ON artwork_tag (artwork_id)');
        $this->addSql('CREATE INDEX IDX_B9EB001EBAD26311 ON artwork_tag (tag_id)');
        $this->addSql('CREATE TABLE posts (id VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content TEXT NOT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_885DBAFA989D9B62 ON posts (slug)');
        $this->addSql('CREATE TABLE sync_logs (id VARCHAR(36) NOT NULL, executed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created INT NOT NULL, updated INT NOT NULL, unchanged INT NOT NULL, log TEXT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE tags (id VARCHAR(36) NOT NULL, name VARCHAR(50) NOT NULL, slug VARCHAR(50) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6FBC9426989D9B62 ON tags (slug)');

        // Add FK constraints after all tables exist with correct types
        $this->addSql('ALTER TABLE artwork_category ADD CONSTRAINT FK_FA06D53FDB8FFA4 FOREIGN KEY (artwork_id) REFERENCES artworks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artwork_category ADD CONSTRAINT FK_FA06D53F12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artwork_tag ADD CONSTRAINT FK_B9EB001EDB8FFA4 FOREIGN KEY (artwork_id) REFERENCES artworks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artwork_tag ADD CONSTRAINT FK_B9EB001EBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE illustration_categories (illustration_id UUID NOT NULL, category_id UUID NOT NULL, PRIMARY KEY (illustration_id, category_id))');
        $this->addSql('CREATE INDEX idx_b3912c8d12469de2 ON illustration_categories (category_id)');
        $this->addSql('CREATE INDEX idx_b3912c8d5926566c ON illustration_categories (illustration_id)');
        $this->addSql('CREATE TABLE illustrations (id UUID NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, price NUMERIC(10, 2) DEFAULT NULL, image_filename VARCHAR(255) NOT NULL, shop_url VARCHAR(500) DEFAULT NULL, is_available BOOLEAN NOT NULL, sort_order INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE illustration_categories ADD CONSTRAINT fk_b3912c8d5926566c FOREIGN KEY (illustration_id) REFERENCES illustrations (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE illustration_categories ADD CONSTRAINT fk_b3912c8d12469de2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE artwork_category DROP CONSTRAINT FK_FA06D53FDB8FFA4');
        $this->addSql('ALTER TABLE artwork_category DROP CONSTRAINT FK_FA06D53F12469DE2');
        $this->addSql('ALTER TABLE artwork_tag DROP CONSTRAINT FK_B9EB001EDB8FFA4');
        $this->addSql('ALTER TABLE artwork_tag DROP CONSTRAINT FK_B9EB001EBAD26311');
        $this->addSql('DROP TABLE about_page');
        $this->addSql('DROP TABLE artworks');
        $this->addSql('DROP TABLE artwork_category');
        $this->addSql('DROP TABLE artwork_tag');
        $this->addSql('DROP TABLE posts');
        $this->addSql('DROP TABLE sync_logs');
        $this->addSql('DROP TABLE tags');
        $this->addSql('ALTER TABLE categories ALTER id TYPE UUID');
    }
}
