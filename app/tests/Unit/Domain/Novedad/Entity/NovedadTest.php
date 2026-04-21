<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Novedad\Entity;

use App\Domain\Novedad\Entity\Novedad;
use App\Domain\Novedad\Event\NovedadCreated;
use App\Domain\Novedad\Event\NovedadDeleted;
use App\Domain\Novedad\Event\NovedadUpdated;
use App\Domain\Novedad\ValueObject\NovedadId;
use App\Domain\Novedad\ValueObject\NovedadTipo;
use PHPUnit\Framework\TestCase;

final class NovedadTest extends TestCase
{
    public function test_create__should_dispatch_novedad_created_event(): void
    {
        $novedad = $this->buildNovedad();
        $events  = $novedad->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(NovedadCreated::class, $events[0]);
    }

    public function test_create__event_should_contain_novedad_id(): void
    {
        $id      = NovedadId::generate();
        $novedad = $this->buildNovedad($id);
        $events  = $novedad->pullDomainEvents();

        $this->assertSame($id->value(), $events[0]->aggregateId());
    }

    public function test_create__should_store_all_fields_correctly(): void
    {
        $id      = NovedadId::generate();
        $novedad = $this->buildNovedad($id);

        $this->assertSame($id->value(), $novedad->id()->value());
        $this->assertSame('Exposición colectiva', $novedad->titulo());
        $this->assertSame('Group exhibition', $novedad->tituloEn());
        $this->assertSame('Contenido de la novedad', $novedad->contenido());
        $this->assertSame(NovedadTipo::Evento, $novedad->tipo());
        $this->assertSame('2026-06-01', $novedad->fecha()->format('Y-m-d'));
        $this->assertSame('Madrid', $novedad->lugar());
        $this->assertSame('https://example.com', $novedad->url());
        $this->assertSame('exposicion-colectiva', $novedad->slug());
        $this->assertTrue($novedad->publicado());
    }

    public function test_update__should_dispatch_novedad_updated_event(): void
    {
        $novedad = $this->buildNovedad();
        $novedad->pullDomainEvents();

        $novedad->update(
            titulo:      'Nuevo título',
            tituloEn:    null,
            contenido:   null,
            contenidoEn: null,
            tipo:        NovedadTipo::Noticia,
            fecha:       new \DateTimeImmutable('2026-07-01'),
            fechaFin:    null,
            imagen:      null,
            lugar:       null,
            url:         null,
            slug:        'nuevo-titulo',
            publicado:   false,
        );

        $events = $novedad->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(NovedadUpdated::class, $events[0]);
    }

    public function test_update__should_change_fields(): void
    {
        $novedad = $this->buildNovedad();
        $novedad->pullDomainEvents();

        $novedad->update(
            titulo:      'Título actualizado',
            tituloEn:    'Updated title',
            contenido:   'Nuevo contenido',
            contenidoEn: null,
            tipo:        NovedadTipo::Noticia,
            fecha:       new \DateTimeImmutable('2026-08-15'),
            fechaFin:    null,
            imagen:      null,
            lugar:       null,
            url:         null,
            slug:        'titulo-actualizado',
            publicado:   false,
        );

        $this->assertSame('Título actualizado', $novedad->titulo());
        $this->assertSame('Updated title', $novedad->tituloEn());
        $this->assertSame('Nuevo contenido', $novedad->contenido());
        $this->assertSame(NovedadTipo::Noticia, $novedad->tipo());
        $this->assertSame('2026-08-15', $novedad->fecha()->format('Y-m-d'));
        $this->assertNull($novedad->lugar());
        $this->assertFalse($novedad->publicado());
    }

    public function test_mark_as_deleted__should_dispatch_novedad_deleted_event(): void
    {
        $novedad = $this->buildNovedad();
        $novedad->pullDomainEvents();

        $novedad->markAsDeleted();

        $events = $novedad->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(NovedadDeleted::class, $events[0]);
    }

    public function test_pull_domain_events__should_clear_events_after_pulling(): void
    {
        $novedad = $this->buildNovedad();
        $novedad->pullDomainEvents();

        $this->assertCount(0, $novedad->pullDomainEvents());
    }

    public function test_titulo_for_locale__when_locale_es__should_return_spanish(): void
    {
        $novedad = $this->buildNovedad();

        $this->assertSame('Exposición colectiva', $novedad->tituloForLocale('es'));
    }

    public function test_titulo_for_locale__when_locale_en_and_translation_exists__should_return_english(): void
    {
        $novedad = $this->buildNovedad();

        $this->assertSame('Group exhibition', $novedad->tituloForLocale('en'));
    }

    public function test_titulo_for_locale__when_locale_en_and_no_translation__should_fallback_to_spanish(): void
    {
        $novedad = $this->buildNovedad(tituloEn: null);

        $this->assertSame('Exposición colectiva', $novedad->tituloForLocale('en'));
    }

    private function buildNovedad(?NovedadId $id = null, ?string $tituloEn = 'Group exhibition'): Novedad
    {
        return Novedad::create(
            id:          $id ?? NovedadId::generate(),
            titulo:      'Exposición colectiva',
            tituloEn:    $tituloEn,
            contenido:   'Contenido de la novedad',
            contenidoEn: null,
            tipo:        NovedadTipo::Evento,
            fecha:       new \DateTimeImmutable('2026-06-01'),
            fechaFin:    null,
            imagen:      null,
            lugar:       'Madrid',
            url:         'https://example.com',
            slug:        'exposicion-colectiva',
            publicado:   true,
        );
    }
}
