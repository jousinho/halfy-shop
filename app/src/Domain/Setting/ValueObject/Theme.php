<?php

declare(strict_types=1);

namespace App\Domain\Setting\ValueObject;

enum Theme: string
{
    case Default  = 'default';
    case Galeria  = 'galeria';
    case Estudio  = 'estudio';
    case Polaroid = 'polaroid';
    case Neon     = 'neon';
    case Editorial = 'editorial';

    public function label(): string
    {
        return match($this) {
            Theme::Default   => 'Default',
            Theme::Galeria   => 'Galería',
            Theme::Estudio   => 'Estudio',
            Theme::Polaroid  => 'Polaroid',
            Theme::Neon      => 'Neon',
            Theme::Editorial => 'Editorial',
        };
    }

    public function description(): string
    {
        return match($this) {
            Theme::Default   => 'Diseño original. Fondo claro, tipografía serif, grid con overlay.',
            Theme::Galeria   => 'White cube. Header centrado, grid portrait, título y precio visibles.',
            Theme::Estudio   => 'Dark workshop. Fondo negro, masonry, acento dorado.',
            Theme::Polaroid  => 'Fotos Polaroid rotadas sobre fondo crema. Fuente handwriting.',
            Theme::Neon      => 'Fondo negro profundo, monospace, glow y glitch en hover.',
            Theme::Editorial => 'Sidebar fija, tipografía condensada bold, grid de revista.',
        };
    }
}
