<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateSiteInstagram;

final class UpdateSiteInstagramCommand
{
    public function __construct(public readonly string $url) {}
}
