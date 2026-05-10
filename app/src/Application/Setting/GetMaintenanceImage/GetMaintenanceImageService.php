<?php

declare(strict_types=1);

namespace App\Application\Setting\GetMaintenanceImage;

use App\Domain\Setting\Repository\SettingRepository;

final class GetMaintenanceImageService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(): ?string
    {
        $setting = $this->settingRepository->findByKey('maintenance_image');

        if ($setting === null || $setting->value() === '') {
            return null;
        }

        return $setting->value();
    }
}
