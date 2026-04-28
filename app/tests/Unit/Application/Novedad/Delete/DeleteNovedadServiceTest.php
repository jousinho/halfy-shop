<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Novedad\Delete;

use App\Application\Novedad\Delete\DeleteNovedadCommand;
use App\Application\Novedad\Delete\DeleteNovedadService;
use App\Domain\Novedad\Entity\Novedad;
use App\Domain\Novedad\Event\NovedadDeleted;
use App\Domain\Novedad\Repository\NovedadRepository;
use App\Domain\Novedad\ValueObject\NovedadId;
use App\Domain\Novedad\ValueObject\NovedadTipo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AllowMockObjectsWithoutExpectations]
final class DeleteNovedadServiceTest extends TestCase
{
    private NovedadRepository&MockObject $novedadRepository;
    private EventDispatcherInterface&MockObject $dispatcher;
    private DeleteNovedadService $service;

    protected function setUp(): void
    {
        $this->novedadRepository = $this->createMock(NovedadRepository::class);
        $this->dispatcher        = $this->createMock(EventDispatcherInterface::class);
        $this->service           = new DeleteNovedadService($this->novedadRepository, $this->dispatcher);
    }

    public function test_execute__should_call_delete_on_repository(): void
    {
        $novedad = $this->buildNovedad();
        $this->novedadRepository->method('findById')->willReturn($novedad);

        $this->novedadRepository->expects($this->once())->method('delete')->with($novedad);

        $this->service->execute(DeleteNovedadCommand::create($novedad->id()->value()));
    }

    public function test_execute__should_dispatch_deleted_event(): void
    {
        $novedad = $this->buildNovedad();
        $this->novedadRepository->method('findById')->willReturn($novedad);
        $this->novedadRepository->method('delete');

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(NovedadDeleted::class));

        $this->service->execute(DeleteNovedadCommand::create($novedad->id()->value()));
    }

    public function test_execute__when_novedad_not_found__should_throw_exception(): void
    {
        $this->novedadRepository->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $this->service->execute(DeleteNovedadCommand::create(NovedadId::generate()->value()));
    }

    private function buildNovedad(): Novedad
    {
        $novedad = Novedad::create(
            id:           NovedadId::generate(),
            titulo:       'Novedad a eliminar',
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
            slug:         'novedad-a-eliminar',
            publicado:    true,
        );
        $novedad->pullDomainEvents();

        return $novedad;
    }
}
