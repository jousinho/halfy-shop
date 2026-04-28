<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Novedad\Update;

use App\Application\Novedad\Update\UpdateNovedadCommand;
use App\Application\Novedad\Update\UpdateNovedadService;
use App\Application\Shared\ImageProcessor;
use App\Domain\Novedad\Entity\Novedad;
use App\Domain\Novedad\Event\NovedadUpdated;
use App\Domain\Novedad\Repository\NovedadRepository;
use App\Domain\Novedad\ValueObject\NovedadId;
use App\Domain\Novedad\ValueObject\NovedadTipo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AllowMockObjectsWithoutExpectations]
final class UpdateNovedadServiceTest extends TestCase
{
    private NovedadRepository&MockObject $novedadRepository;
    private ImageProcessor&MockObject $imageProcessor;
    private EventDispatcherInterface&MockObject $dispatcher;
    private UpdateNovedadService $service;

    protected function setUp(): void
    {
        $this->novedadRepository = $this->createMock(NovedadRepository::class);
        $this->imageProcessor    = $this->createMock(ImageProcessor::class);
        $this->dispatcher        = $this->createMock(EventDispatcherInterface::class);
        $this->service           = new UpdateNovedadService($this->novedadRepository, $this->imageProcessor, $this->dispatcher);
    }

    public function test_execute__should_update_fields(): void
    {
        $novedad = $this->buildNovedad();
        $this->novedadRepository->method('findById')->willReturn($novedad);
        $this->novedadRepository->method('slugExists')->willReturn(false);
        $this->dispatcher->method('dispatch');

        $this->service->execute(UpdateNovedadCommand::create(
            id:           $novedad->id()->value(),
            titulo:       'Título actualizado',
            tituloEn:     null,
            contenido:    null,
            contenidoEn:  null,
            tipo:         'noticia',
            fecha:        '2026-09-01',
            fechaFin:     null,
            imagenFile:   null,
            lugar:        null,
            url:          null,
            videoYoutube: null,
            videoReel:    null,
            publicado:    false,
        ));

        $this->assertSame('Título actualizado', $novedad->titulo());
        $this->assertFalse($novedad->publicado());
    }

    public function test_execute__should_save_and_dispatch_updated_event(): void
    {
        $novedad = $this->buildNovedad();
        $this->novedadRepository->method('findById')->willReturn($novedad);
        $this->novedadRepository->method('slugExists')->willReturn(false);

        $this->novedadRepository->expects($this->once())->method('save');
        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(NovedadUpdated::class));

        $this->service->execute(UpdateNovedadCommand::create(
            id:           $novedad->id()->value(),
            titulo:       'Otro título',
            tituloEn:     null,
            contenido:    null,
            contenidoEn:  null,
            tipo:         'noticia',
            fecha:        '2026-09-01',
            fechaFin:     null,
            imagenFile:   null,
            lugar:        null,
            url:          null,
            videoYoutube: null,
            videoReel:    null,
            publicado:    true,
        ));
    }

    public function test_execute__when_novedad_not_found__should_throw_exception(): void
    {
        $this->novedadRepository->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $this->service->execute(UpdateNovedadCommand::create(
            id:           NovedadId::generate()->value(),
            titulo:       'X',
            tituloEn:     null,
            contenido:    null,
            contenidoEn:  null,
            tipo:         'noticia',
            fecha:        '2026-09-01',
            fechaFin:     null,
            imagenFile:   null,
            lugar:        null,
            url:          null,
            videoYoutube: null,
            videoReel:    null,
            publicado:    true,
        ));
    }

    private function buildNovedad(): Novedad
    {
        $novedad = Novedad::create(
            id:           NovedadId::generate(),
            titulo:       'Novedad original',
            tituloEn:     null,
            contenido:    null,
            contenidoEn:  null,
            tipo:         NovedadTipo::Noticia,
            fecha:        new \DateTimeImmutable('2026-06-01'),
            fechaFin:     null,
            imagen:       null,
            lugar:        null,
            url:          null,
            videoYoutube: null,
            videoReel:    null,
            slug:         'novedad-original',
            publicado:    true,
        );
        $novedad->pullDomainEvents();

        return $novedad;
    }
}
