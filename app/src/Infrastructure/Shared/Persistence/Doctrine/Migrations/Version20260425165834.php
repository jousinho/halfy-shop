<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260425165834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make technique, dimensions, year and image_filename nullable — only title is required for artworks';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artworks ALTER technique DROP NOT NULL');
        $this->addSql('ALTER TABLE artworks ALTER dimensions DROP NOT NULL');
        $this->addSql('ALTER TABLE artworks ALTER year DROP NOT NULL');
        $this->addSql('ALTER TABLE artworks ALTER image_filename DROP NOT NULL');
        $this->addSql('ALTER TABLE artworks ALTER visible DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artworks ALTER technique SET NOT NULL');
        $this->addSql('ALTER TABLE artworks ALTER dimensions SET NOT NULL');
        $this->addSql('ALTER TABLE artworks ALTER year SET NOT NULL');
        $this->addSql('ALTER TABLE artworks ALTER image_filename SET NOT NULL');
        $this->addSql('ALTER TABLE artworks ALTER visible SET DEFAULT true');
    }
}
