<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed default maintenance_text setting';
    }

    public function up(Schema $schema): void
    {
        $defaultText = 'Hola, estoy construyendo mi nueva web.<br>Puedes ver mis ilustraciones en <a href="https://annapownall.bigcartel.com/" target="_blank" rel="noopener">annapownall.bigcartel.com</a> mientras la acabo.';
        $this->addSql('INSERT IGNORE INTO settings (setting_key, value) VALUES (:key, :value)', [
            'key'   => 'maintenance_text',
            'value' => $defaultText,
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM settings WHERE setting_key = 'maintenance_text'");
    }
}
