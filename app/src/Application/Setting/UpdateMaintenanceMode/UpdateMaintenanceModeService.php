<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateMaintenanceMode;

use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;

final class UpdateMaintenanceModeService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(UpdateMaintenanceModeCommand $command): void
    {
        $setting = $this->settingRepository->findByKey('maintenance_mode');
        $value   = $command->enabled ? '1' : '0';

        if ($setting === null) {
            $setting = Setting::create('maintenance_mode', $value);
        } else {
            $setting->setValue($value);
        }

        $this->settingRepository->save($setting);
    }
}
