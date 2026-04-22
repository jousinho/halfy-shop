<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateSiteInstagram;

use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;

final class UpdateSiteInstagramService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(UpdateSiteInstagramCommand $command): void
    {
        $setting = $this->settingRepository->findByKey('site_instagram');

        if ($setting === null) {
            $setting = Setting::create('site_instagram', $command->url);
        } else {
            $setting->setValue($command->url);
        }

        $this->settingRepository->save($setting);
    }
}
