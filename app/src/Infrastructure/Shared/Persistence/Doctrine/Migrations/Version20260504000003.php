<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename settings.key → settings.setting_key (key is reserved in MySQL 8)';
    }

    public function up(Schema $schema): void
    {
        // Column already named setting_key in Version20260504000001; no-op.
    }

    public function down(Schema $schema): void
    {
    }
}
