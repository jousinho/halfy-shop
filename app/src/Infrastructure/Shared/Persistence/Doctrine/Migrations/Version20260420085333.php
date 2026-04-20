<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260420085333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE about_page ADD content_en TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE artworks ADD title_en VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE artworks ADD description_en TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE categories ADD name_en VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE about_page DROP content_en');
        $this->addSql('ALTER TABLE artworks DROP title_en');
        $this->addSql('ALTER TABLE artworks DROP description_en');
        $this->addSql('ALTER TABLE categories DROP name_en');
    }
}
