<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add settings table for site configuration (active theme, etc.)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE settings (key VARCHAR(100) NOT NULL, value TEXT NOT NULL, PRIMARY KEY (key))');
        $this->addSql("INSERT INTO settings (key, value) VALUES ('active_theme', 'default')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE settings');
    }
}
