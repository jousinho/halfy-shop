<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateSiteLogo;

final class UpdateSiteLogoCommand
{
    public function __construct(public readonly string $filename) {}
}
