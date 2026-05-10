<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed default maintenance_mode and maintenance_image settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT IGNORE INTO settings (setting_key, value) VALUES ('maintenance_mode', '1')");
        $this->addSql("INSERT IGNORE INTO settings (setting_key, value) VALUES ('maintenance_image', '')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM settings WHERE setting_key IN ('maintenance_mode', 'maintenance_image')");
    }
}
