<?php

declare(strict_types=1);

namespace App\Application\Novedad\Create;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CreateNovedadCommand
{
    private function __construct(
        public readonly string $titulo,
        public readonly ?string $tituloEn,
        public readonly ?string $contenido,
        public readonly ?string $contenidoEn,
        public readonly string $tipo,
        public readonly string $fecha,
        public readonly ?string $fechaFin,
        public readonly ?UploadedFile $imagenFile,
        public readonly ?string $lugar,
        public readonly ?string $url,
        public readonly ?string $videoYoutube,
        public readonly ?string $videoReel,
        public readonly bool $publicado,
    ) {}

    public static function create(
        string $titulo,
        ?string $tituloEn,
        ?string $contenido,
        ?string $contenidoEn,
        string $tipo,
        string $fecha,
        ?string $fechaFin,
        ?UploadedFile $imagenFile,
        ?string $lugar,
        ?string $url,
        ?string $videoYoutube,
        ?string $videoReel,
        bool $publicado,
    ): self {
        return new self(
            $titulo, $tituloEn, $contenido, $contenidoEn,
            $tipo, $fecha, $fechaFin, $imagenFile, $lugar, $url, $videoYoutube, $videoReel, $publicado,
        );
    }
}
