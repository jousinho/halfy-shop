<?php

declare(strict_types=1);

namespace App\Domain\Setting\ValueObject;

enum Theme: string
{
    case Default = 'default';
    case Galeria = 'galeria';
    case Estudio = 'estudio';

    public function label(): string
    {
        return match($this) {
            Theme::Default => 'Default',
            Theme::Galeria => 'Galería',
            Theme::Estudio => 'Estudio',
        };
    }

    public function description(): string
    {
        return match($this) {
            Theme::Default => 'Diseño original. Fondo claro, tipografía serif, grid de obras con overlay.',
            Theme::Galeria => 'White cube. Header centrado, grid de 4 columnas portrait, título y precio siempre visibles.',
            Theme::Estudio => 'Dark workshop. Fondo negro, sans-serif, layout masonry de imágenes a altura natural.',
        };
    }
}
