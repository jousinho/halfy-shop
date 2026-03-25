<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Assigns categories to artworks based on technique field + title matching from annapownall.com RSS.
 *
 * Category IDs (from categories table):
 *   grabado     e32d74fb-090b-427c-b947-877208d9b6bc
 *   aguafuerte  4021913f-31a1-4bf8-91e0-316bb682d23d
 *   ilustración 34e5116e-874c-4f9b-91f8-1a9d3c7ec156
 *   acuarela    1f292dfe-dac4-4d26-8a54-958f9f7e26e1
 *   print       b31a5184-3e4a-4e0a-9283-eccead43b1d6
 *   bastidores  f504fcb1-274f-4508-ad30-11820377defd
 *   original    4d162f97-78e9-433e-a6bc-4c0b515e77c0
 *   Tote bag    a5de807b-4930-4576-ae8e-1d4dc73c4dfb
 */
final class Version20260325150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Assign categories to artworks from technique field and RSS cross-reference';
    }

    public function up(Schema $schema): void
    {
        // Fotopolímero → grabado
        $this->addSql(<<<SQL
            INSERT INTO artwork_category (artwork_id, category_id)
            SELECT a.id, 'e32d74fb-090b-427c-b947-877208d9b6bc'
            FROM artworks a
            WHERE a.technique ILIKE '%fotopolímero%'
            ON CONFLICT DO NOTHING
        SQL);

        // Aguafuerte (incluye aguafuerte + aguatinta) → aguafuerte
        $this->addSql(<<<SQL
            INSERT INTO artwork_category (artwork_id, category_id)
            SELECT a.id, '4021913f-31a1-4bf8-91e0-316bb682d23d'
            FROM artworks a
            WHERE a.technique ILIKE '%aguafuerte%'
            ON CONFLICT DO NOTHING
        SQL);

        // Print sobre papel → print
        $this->addSql(<<<SQL
            INSERT INTO artwork_category (artwork_id, category_id)
            SELECT a.id, 'b31a5184-3e4a-4e0a-9283-eccead43b1d6'
            FROM artworks a
            WHERE a.technique ILIKE '%print%'
            ON CONFLICT DO NOTHING
        SQL);

        // Acuarela montada sobre bastidor → bastidores
        $this->addSql(<<<SQL
            INSERT INTO artwork_category (artwork_id, category_id)
            SELECT a.id, 'f504fcb1-274f-4508-ad30-11820377defd'
            FROM artworks a
            WHERE a.technique ILIKE '%bastidor%'
            ON CONFLICT DO NOTHING
        SQL);

        // Acuarela sin bastidor y sin print (evitar "papel de acuarela" en prints) → acuarela
        $this->addSql(<<<SQL
            INSERT INTO artwork_category (artwork_id, category_id)
            SELECT a.id, '1f292dfe-dac4-4d26-8a54-958f9f7e26e1'
            FROM artworks a
            WHERE a.technique ILIKE '%acuarela%'
              AND a.technique NOT ILIKE '%bastidor%'
              AND a.technique NOT ILIKE '%print%'
            ON CONFLICT DO NOTHING
        SQL);

        // Ilustración (RSS: Lectoras, My Garden, Casa Jardín, Spring Blues, Cosmos Blues)
        // Son prints de ilustraciones originales → categoría ilustración
        $this->addSql(<<<SQL
            INSERT INTO artwork_category (artwork_id, category_id)
            SELECT a.id, '34e5116e-874c-4f9b-91f8-1a9d3c7ec156'
            FROM artworks a
            WHERE a.technique ILIKE '%print%'
            ON CONFLICT DO NOTHING
        SQL);

        // Tote bag
        $this->addSql(<<<SQL
            INSERT INTO artwork_category (artwork_id, category_id)
            SELECT a.id, 'a5de807b-4930-4576-ae8e-1d4dc73c4dfb'
            FROM artworks a
            WHERE a.title ILIKE '%tote bag%'
            ON CONFLICT DO NOTHING
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM artwork_category');
    }
}
