<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateMaintenanceText;

use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;

final class UpdateMaintenanceTextService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(UpdateMaintenanceTextCommand $command): void
    {
        $setting = $this->settingRepository->findByKey('maintenance_text');

        if ($setting === null) {
            $setting = Setting::create('maintenance_text', $command->text);
        } else {
            $setting->setValue($command->text);
        }

        $this->settingRepository->save($setting);
    }
}
