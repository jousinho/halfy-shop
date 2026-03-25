<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed about_page with content imported from annapownall.com';
    }

    public function up(Schema $schema): void
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
            'INSERT INTO about_page (id, content, photo_filename, updated_at) VALUES (:id, :content, NULL, :updated_at)',
            [
                'id'         => '00000000-0000-0000-0000-000000000001',
                'content'    => $content,
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM about_page WHERE id = '00000000-0000-0000-0000-000000000001'");
    }
}
