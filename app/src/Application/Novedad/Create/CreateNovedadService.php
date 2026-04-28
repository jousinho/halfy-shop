<?php

declare(strict_types=1);

namespace App\Application\Novedad\Create;

use App\Application\Shared\ImageProcessor;
use App\Domain\Novedad\Entity\Novedad;
use App\Domain\Novedad\Repository\NovedadRepository;
use App\Domain\Novedad\ValueObject\NovedadId;
use App\Domain\Novedad\ValueObject\NovedadTipo;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class CreateNovedadService
{
    public function __construct(
        private readonly NovedadRepository $novedadRepository,
        private readonly ImageProcessor $imageProcessor,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public function execute(CreateNovedadCommand $command): void
    {
        $imagen  = $command->imagenFile !== null
            ? $this->imageProcessor->process($command->imagenFile, 'novedades')
            : null;

        $novedad = Novedad::create(
            id:           NovedadId::generate(),
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
            slug:         $this->buildSlug($command->titulo),
            publicado:    $command->publicado,
        );

        $this->novedadRepository->save($novedad);

        foreach ($novedad->pullDomainEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }
    }

    private function buildSlug(string $titulo): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $this->transliterate($titulo)), '-'));
        $slug = $base;
        $i    = 1;

        while ($this->novedadRepository->slugExists($slug)) {
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
