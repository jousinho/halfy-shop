<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateActiveTheme;

use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;

final class UpdateActiveThemeService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(UpdateActiveThemeCommand $command): void
    {
        $setting = $this->settingRepository->findByKey('active_theme');

        if ($setting === null) {
            $setting = Setting::create('active_theme', $command->theme->value);
        } else {
            $setting->setValue($command->theme->value);
        }

        $this->settingRepository->save($setting);
    }
}
