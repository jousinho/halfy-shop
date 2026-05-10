<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateMaintenanceText;

final class UpdateMaintenanceTextCommand
{
    public function __construct(public readonly string $text) {}
}
