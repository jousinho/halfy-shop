<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateMaintenanceMode;

final class UpdateMaintenanceModeCommand
{
    public function __construct(public readonly bool $enabled) {}
}
