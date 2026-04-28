<?php

declare(strict_types=1);

namespace App\Application\Novedad\Update;

use App\Application\Shared\ImageProcessor;
use App\Domain\Novedad\Repository\NovedadRepository;
use App\Domain\Novedad\ValueObject\NovedadId;
use App\Domain\Novedad\ValueObject\NovedadTipo;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class UpdateNovedadService
{
    public function __construct(
        private readonly NovedadRepository $novedadRepository,
        private readonly ImageProcessor $imageProcessor,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public function execute(UpdateNovedadCommand $command): void
    {
        $novedad = $this->novedadRepository->findById(NovedadId::create($command->id));

        if ($novedad === null) {
            throw new \RuntimeException(sprintf('Novedad "%s" not found.', $command->id));
        }

        $imagen = $command->imagenFile !== null
            ? $this->imageProcessor->process($command->imagenFile, 'novedades')
            : null;

        $slug = $this->buildSlug($command->titulo, $command->id);

        $novedad->update(
            titulo:       $command->titulo,
            tituloEn:     $command->tituloEn,
            contenido:    $command->contenido,
            contenidoEn:  $command->contenidoEn,
            tipo:         NovedadTipo::from($command->tipo),
            fecha:        new \DateTimeImmutable($command->fecha),
            fechaFin:     $command->fechaFin !== null ? new \DateTimeImmutable($command->fechaFin) : null,
            imagen:       $imagen,
            lugar:        $command->lugar,
            url:          $command->url,
            videoYoutube: $command->videoYoutube,
            videoReel:    $command->videoReel,
            slug:         $slug,
            publicado:    $command->publicado,
        );

        $this->novedadRepository->save($novedad);

        foreach ($novedad->pullDomainEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }
    }

    private function buildSlug(string $titulo, string $excludeId): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $this->transliterate($titulo)), '-'));
        $slug = $base;
        $i    = 1;

        while ($this->novedadRepository->slugExists($slug, $excludeId)) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function transliterate(string $text): string
    {
        $map = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n',
                'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ü'=>'u','Ñ'=>'n'];
        return strtr($text, $map);
    }
}
