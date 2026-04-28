<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateSiteFavicon;

final class UpdateSiteFaviconCommand
{
    public function __construct(public readonly string $filename) {}
}
