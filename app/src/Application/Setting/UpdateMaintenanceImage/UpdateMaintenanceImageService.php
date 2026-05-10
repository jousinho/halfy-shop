<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateMaintenanceImage;

use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;

final class UpdateMaintenanceImageService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(UpdateMaintenanceImageCommand $command): void
    {
        $setting = $this->settingRepository->findByKey('maintenance_image');

        if ($setting === null) {
            $setting = Setting::create('maintenance_image', $command->filename);
        } else {
            $setting->setValue($command->filename);
        }

        $this->settingRepository->save($setting);
    }
}
