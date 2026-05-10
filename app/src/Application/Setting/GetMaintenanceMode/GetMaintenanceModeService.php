<?php

declare(strict_types=1);

namespace App\Application\Setting\GetMaintenanceMode;

use App\Domain\Setting\Repository\SettingRepository;

final class GetMaintenanceModeService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(): bool
    {
        $setting = $this->settingRepository->findByKey('maintenance_mode');

        return $setting === null || $setting->value() === '1';
    }
}
