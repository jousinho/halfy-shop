<?php

declare(strict_types=1);

namespace App\Application\Setting\GetMaintenanceText;

use App\Domain\Setting\Repository\SettingRepository;

final class GetMaintenanceTextService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(): string
    {
        $setting = $this->settingRepository->findByKey('maintenance_text');

        return $setting?->value() ?? '';
    }
}
