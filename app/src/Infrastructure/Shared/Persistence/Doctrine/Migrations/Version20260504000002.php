<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed data: about_page content and artwork category assignments by technique';
    }

    public function up(Schema $schema): void
    {
        $this->seedAboutPage();
        $this->seedArtworkCategories();
    }

    private function seedAboutPage(): void
    {
        $content = <<<HTML
<h2>Formación</h2>
<ul>
    <li>Licenciada en la especialidad de grabado por la Facultad de Bellas Artes de la U.C.M.</li>
    <li>Módulo de grabado calcográfico en el Centro Internacional de la Estampa Contemporánea en Betanzos, A Coruña (Marzo y Abril de 2011)</li>
</ul>

<h2>Ilustración</h2>
<ul>
    <li>Serie de 10 ilustraciones para la página «La aventura de ser madre» del grupo El Corte Inglés (Febrero 2011)</li>
    <li>Realización de ilustraciones para la ONG Artsur las Segovias (Septiembre 2007)</li>
    <li>Realización de ilustraciones para la ONG Arcsur-Las Segovias (Agosto 2006)</li>
    <li>Realización de las ilustraciones de la colección «Aprendo español con cuentos», compuesta por seis libros. Editorial Sgel (2004–2005)</li>
    <li>Ilustraciones publicitarias para el grupo Pharmaconsult S.A. (2002)</li>
</ul>

<h2>Grabado</h2>
<ul>
    <li>X Feria Internacional de Grabado FIG, Bilbao. Noviembre 2021</li>
    <li>VIII Feria Internacional de Grabado FIG, Bilbao. Noviembre 2019</li>
    <li>VII Feria Internacional de Grabado FIG, Bilbao. Noviembre 2018</li>
    <li>III Feria Internacional de Grabado FIG, Bilbao. Noviembre 2014</li>
    <li>Exposición colectiva con el grupo PA Grabadores en la Casa de Cultura de Ribadesella, Asturias. Julio 2014</li>
    <li>Exposición colectiva con el grupo PA Grabadores en el colegio Rosales, Madrid. Febrero 2014</li>
    <li>II Feria Internacional de Grabado FIG, Bilbao. Diciembre 2013</li>
    <li>I Feria Internacional del Grabado FIG, Bilbao. Diciembre 2012</li>
    <li>Obra seleccionada para el catálogo y exposición del concurso Jóvenes Creadores 2000 de la Calcografía Nacional</li>
    <li>«Introducción al grabado para niños. Creación a partir de materiales reciclados y de desecho». La Casa Encendida, Madrid. Julio 2011</li>
    <li>«Introducción al grabado calcográfico». Camarote, Madrid. Junio 2011</li>
</ul>
HTML;

        $this->addSql(
            'INSERT IGNORE INTO about_page (id, content, photo_filename, updated_at) VALUES (:id, :content, NULL, :updated_at)',
            [
                'id'         => '00000000-0000-0000-0000-000000000001',
                'content'    => $content,
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * Assigns categories to artworks based on technique field.
     * Run `app:sync:bigcartel` before this migration executes on a fresh DB,
     * otherwise artworks table is empty and 0 rows will be inserted.
     *
     * Category IDs:
     *   grabado     e32d74fb-090b-427c-b947-877208d9b6bc
     *   aguafuerte  4021913f-31a1-4bf8-91e0-316bb682d23d
     *   ilustración 34e5116e-874c-4f9b-91f8-1a9d3c7ec156
     *   acuarela    1f292dfe-dac4-4d26-8a54-958f9f7e26e1
     *   print       b31a5184-3e4a-4e0a-9283-eccead43b1d6
     *   bastidores  f504fcb1-274f-4508-ad30-11820377defd
     *   original    4d162f97-78e9-433e-a6bc-4c0b515e77c0
     *   Tote bag    a5de807b-4930-4576-ae8e-1d4dc73c4dfb
     */
    private function seedArtworkCategories(): void
    {
        // LIKE is case-insensitive with utf8mb4_unicode_ci, same behaviour as PostgreSQL ILIKE
        // INSERT IGNORE skips duplicates, equivalent to ON CONFLICT DO NOTHING

        $this->addSql("INSERT IGNORE INTO artwork_category (artwork_id, category_id)
            SELECT a.id, 'e32d74fb-090b-427c-b947-877208d9b6bc'
            FROM artworks a
            WHERE a.technique LIKE '%fotopolímero%'");

        $this->addSql("INSERT IGNORE INTO artwork_category (artwork_id, category_id)
            SELECT a.id, '4021913f-31a1-4bf8-91e0-316bb682d23d'
            FROM artworks a
            WHERE a.technique LIKE '%aguafuerte%'");

        $this->addSql("INSERT IGNORE INTO artwork_category (artwork_id, category_id)
            SELECT a.id, 'b31a5184-3e4a-4e0a-9283-eccead43b1d6'
            FROM artworks a
            WHERE a.technique LIKE '%print%'");

        $this->addSql("INSERT IGNORE INTO artwork_category (artwork_id, category_id)
            SELECT a.id, 'f504fcb1-274f-4508-ad30-11820377defd'
            FROM artworks a
            WHERE a.technique LIKE '%bastidor%'");

        $this->addSql("INSERT IGNORE INTO artwork_category (artwork_id, category_id)
            SELECT a.id, '1f292dfe-dac4-4d26-8a54-958f9f7e26e1'
            FROM artworks a
            WHERE a.technique LIKE '%acuarela%'
              AND a.technique NOT LIKE '%bastidor%'
              AND a.technique NOT LIKE '%print%'");

        $this->addSql("INSERT IGNORE INTO artwork_category (artwork_id, category_id)
            SELECT a.id, '34e5116e-874c-4f9b-91f8-1a9d3c7ec156'
            FROM artworks a
            WHERE a.technique LIKE '%print%'");

        $this->addSql("INSERT IGNORE INTO artwork_category (artwork_id, category_id)
            SELECT a.id, 'a5de807b-4930-4576-ae8e-1d4dc73c4dfb'
            FROM artworks a
            WHERE a.title LIKE '%tote bag%'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM about_page WHERE id = '00000000-0000-0000-0000-000000000001'");
        $this->addSql('DELETE FROM artwork_category');
    }
}
