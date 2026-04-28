<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateSiteContactEmail;

use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;

final class UpdateSiteContactEmailService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(UpdateSiteContactEmailCommand $command): void
    {
        $setting = $this->settingRepository->findByKey('site_contact_email');

        if ($setting === null) {
            $setting = Setting::create('site_contact_email', $command->email);
        } else {
            $setting->setValue($command->email);
        }

        $this->settingRepository->save($setting);
    }
}
