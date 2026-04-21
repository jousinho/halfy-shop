<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260421175558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE novedades (id VARCHAR(36) NOT NULL, titulo VARCHAR(255) NOT NULL, titulo_en VARCHAR(255) DEFAULT NULL, contenido TEXT DEFAULT NULL, contenido_en TEXT DEFAULT NULL, tipo VARCHAR(20) NOT NULL, fecha DATE NOT NULL, lugar VARCHAR(255) DEFAULT NULL, url VARCHAR(500) DEFAULT NULL, slug VARCHAR(255) NOT NULL, publicado BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DD921A04989D9B62 ON novedades (slug)');
        $this->addSql('ALTER TABLE artwork_tag DROP CONSTRAINT fk_b9eb001edb8ffa4');
        $this->addSql('ALTER TABLE artwork_tag DROP CONSTRAINT fk_b9eb001ebad26311');
        $this->addSql('DROP TABLE artwork_tag');
        $this->addSql('DROP TABLE posts');
        $this->addSql('DROP TABLE tags');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artwork_tag (artwork_id VARCHAR(36) NOT NULL, tag_id VARCHAR(36) NOT NULL, PRIMARY KEY (artwork_id, tag_id))');
        $this->addSql('CREATE INDEX idx_b9eb001edb8ffa4 ON artwork_tag (artwork_id)');
        $this->addSql('CREATE INDEX idx_b9eb001ebad26311 ON artwork_tag (tag_id)');
        $this->addSql('CREATE TABLE posts (id VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content TEXT NOT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_885dbafa989d9b62 ON posts (slug)');
        $this->addSql('CREATE TABLE tags (id VARCHAR(36) NOT NULL, name VARCHAR(50) NOT NULL, slug VARCHAR(50) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_6fbc9426989d9b62 ON tags (slug)');
        $this->addSql('ALTER TABLE artwork_tag ADD CONSTRAINT fk_b9eb001edb8ffa4 FOREIGN KEY (artwork_id) REFERENCES artworks (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE artwork_tag ADD CONSTRAINT fk_b9eb001ebad26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE novedades');
    }
}
