<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateAnnouncement;

use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;

final class UpdateAnnouncementService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(UpdateAnnouncementCommand $command): void
    {
        $this->upsert('announcement_enabled', $command->enabled ? '1' : '0');
        $this->upsert('announcement_text', $command->text);
    }

    private function upsert(string $key, string $value): void
    {
        $setting = $this->settingRepository->findByKey($key);

        if ($setting === null) {
            $setting = Setting::create($key, $value);
        } else {
            $setting->setValue($value);
        }

        $this->settingRepository->save($setting);
    }
}
