<?php

declare(strict_types=1);

namespace App\Domain\Novedad\ValueObject;

enum NovedadTipo: string
{
    case Noticia = 'noticia';
    case Evento  = 'evento';
}
