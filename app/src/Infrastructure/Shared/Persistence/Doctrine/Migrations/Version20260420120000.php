<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420120000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE artwork_tag DROP CONSTRAINT fk_b9eb001ebad26311');
        $this->addSql('ALTER TABLE artwork_tag DROP CONSTRAINT fk_b9eb001edb8ffa4');
        $this->addSql('DROP TABLE artwork_tag');
        $this->addSql('DROP TABLE tags');
        $this->addSql('DROP TABLE posts');

        $this->addSql('CREATE TABLE novedades (
            id VARCHAR(36) NOT NULL,
            titulo VARCHAR(255) NOT NULL,
            titulo_en VARCHAR(255) DEFAULT NULL,
            contenido TEXT DEFAULT NULL,
            contenido_en TEXT DEFAULT NULL,
            tipo VARCHAR(20) NOT NULL,
            fecha DATE NOT NULL,
            lugar VARCHAR(255) DEFAULT NULL,
            url VARCHAR(500) DEFAULT NULL,
            slug VARCHAR(255) NOT NULL,
            publicado BOOLEAN NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN novedades.fecha IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN novedades.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_novedades_slug ON novedades (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE novedades');

        $this->addSql('CREATE TABLE tags (
            id VARCHAR(36) NOT NULL,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE artwork_tag (
            artwork_id VARCHAR(36) NOT NULL,
            tag_id VARCHAR(36) NOT NULL,
            PRIMARY KEY(artwork_id, tag_id)
        )');
        $this->addSql('CREATE TABLE posts (
            id VARCHAR(36) NOT NULL,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            content TEXT DEFAULT NULL,
            published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            is_published BOOLEAN NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
    }
}
