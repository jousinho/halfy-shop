<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateSiteContactImage;

use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;

final class UpdateSiteContactImageService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(UpdateSiteContactImageCommand $command): void
    {
        $setting = $this->settingRepository->findByKey('site_contact_image');

        if ($setting === null) {
            $setting = Setting::create('site_contact_image', $command->filename);
        } else {
            $setting->setValue($command->filename);
        }

        $this->settingRepository->save($setting);
    }
}
