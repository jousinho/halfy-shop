<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateSiteFavicon;

use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;

final class UpdateSiteFaviconService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(UpdateSiteFaviconCommand $command): void
    {
        $setting = $this->settingRepository->findByKey('site_favicon');

        if ($setting === null) {
            $setting = Setting::create('site_favicon', $command->filename);
        } else {
            $setting->setValue($command->filename);
        }

        $this->settingRepository->save($setting);
    }
}
