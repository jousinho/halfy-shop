<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateSiteLogo;

use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;

final class UpdateSiteLogoService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(UpdateSiteLogoCommand $command): void
    {
        $setting = $this->settingRepository->findByKey('site_logo');

        if ($setting === null) {
            $setting = Setting::create('site_logo', $command->filename);
        } else {
            $setting->setValue($command->filename);
        }

        $this->settingRepository->save($setting);
    }
}
