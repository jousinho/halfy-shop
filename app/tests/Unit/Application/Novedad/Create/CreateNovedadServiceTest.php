<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Novedad\Create;

use App\Application\Novedad\Create\CreateNovedadCommand;
use App\Application\Novedad\Create\CreateNovedadService;
use App\Application\Shared\ImageProcessor;
use App\Domain\Novedad\Entity\Novedad;
use App\Domain\Novedad\Event\NovedadCreated;
use App\Domain\Novedad\Repository\NovedadRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AllowMockObjectsWithoutExpectations]
final class CreateNovedadServiceTest extends TestCase
{
    private NovedadRepository&MockObject $novedadRepository;
    private ImageProcessor&MockObject $imageProcessor;
    private EventDispatcherInterface&MockObject $dispatcher;
    private CreateNovedadService $service;

    protected function setUp(): void
    {
        $this->novedadRepository = $this->createMock(NovedadRepository::class);
        $this->imageProcessor    = $this->createMock(ImageProcessor::class);
        $this->dispatcher        = $this->createMock(EventDispatcherInterface::class);
        $this->service           = new CreateNovedadService($this->novedadRepository, $this->imageProcessor, $this->dispatcher);
    }

    public function test_execute__should_save_novedad(): void
    {
        $this->novedadRepository->method('slugExists')->willReturn(false);
        $this->novedadRepository->expects($this->once())->method('save');

        $this->service->execute($this->buildCommand());
    }

    public function test_execute__should_dispatch_created_event(): void
    {
        $this->novedadRepository->method('slugExists')->willReturn(false);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(NovedadCreated::class));

        $this->service->execute($this->buildCommand());
    }

    public function test_execute__should_generate_slug_from_titulo(): void
    {
        $this->novedadRepository->method('slugExists')->willReturn(false);

        $saved = null;
        $this->novedadRepository
            ->method('save')
            ->willReturnCallback(function (Novedad $n) use (&$saved): void {
                $saved = $n;
            });

        $this->service->execute($this->buildCommand(titulo: 'Exposición en Madrid'));

        $this->assertSame('exposicion-en-madrid', $saved->slug());
    }

    public function test_execute__when_slug_already_exists__should_append_suffix(): void
    {
        $this->novedadRepository
            ->method('slugExists')
            ->willReturnOnConsecutiveCalls(true, false);

        $saved = null;
        $this->novedadRepository
            ->method('save')
            ->willReturnCallback(function (Novedad $n) use (&$saved): void {
                $saved = $n;
            });

        $this->service->execute($this->buildCommand(titulo: 'Exposición'));

        $this->assertSame('exposicion-1', $saved->slug());
    }

    public function test_execute__should_set_publicado_correctly(): void
    {
        $this->novedadRepository->method('slugExists')->willReturn(false);

        $saved = null;
        $this->novedadRepository
            ->method('save')
            ->willReturnCallback(function (Novedad $n) use (&$saved): void {
                $saved = $n;
            });

        $this->service->execute($this->buildCommand(publicado: false));

        $this->assertFalse($saved->publicado());
    }

    private function buildCommand(string $titulo = 'Novedad de prueba', bool $publicado = true): CreateNovedadCommand
    {
        return CreateNovedadCommand::create(
            titulo:      $titulo,
            tituloEn:    null,
            contenido:   null,
            contenidoEn: null,
            tipo:        'noticia',
            fecha:       '2026-06-01',
            fechaFin:    null,
            imagenFile:  null,
            lugar:       null,
            url:         null,
            publicado:   $publicado,
        );
    }
}
