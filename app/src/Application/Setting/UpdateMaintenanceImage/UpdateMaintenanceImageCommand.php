<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateMaintenanceImage;

final class UpdateMaintenanceImageCommand
{
    public function __construct(public readonly string $filename) {}
}
