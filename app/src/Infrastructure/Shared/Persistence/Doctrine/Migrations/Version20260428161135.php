<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260428161135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add video_youtube and video_reel columns to novedades';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE novedades ADD video_youtube VARCHAR(500) DEFAULT NULL, ADD video_reel VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE novedades DROP COLUMN video_youtube, DROP COLUMN video_reel');
    }
}
