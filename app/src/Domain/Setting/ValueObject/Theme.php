<?php

declare(strict_types=1);

namespace App\Domain\Setting\ValueObject;

enum Theme: string
{
    case Default = 'default';

    public function label(): string
    {
        return match($this) {
            Theme::Default => 'Default',
        };
    }

    public function description(): string
    {
        return match($this) {
            Theme::Default => 'Diseño original. Fondo oscuro, tipografía serif, grid de obras.',
        };
    }
}
