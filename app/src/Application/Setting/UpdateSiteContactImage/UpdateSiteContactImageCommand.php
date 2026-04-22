<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateSiteContactImage;

final class UpdateSiteContactImageCommand
{
    public function __construct(public readonly string $filename) {}
}
