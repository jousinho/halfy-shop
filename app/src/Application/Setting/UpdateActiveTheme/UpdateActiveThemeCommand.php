<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateActiveTheme;

use App\Domain\Setting\ValueObject\Theme;

final class UpdateActiveThemeCommand
{
    public function __construct(public readonly Theme $theme) {}
}
