<?php

declare(strict_types=1);

namespace App\Application\Setting\GetAnnouncement;

use App\Domain\Setting\Repository\SettingRepository;

final class GetAnnouncementService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    /** @return array{enabled: bool, text: string} */
    public function execute(): array
    {
        $enabledSetting = $this->settingRepository->findByKey('announcement_enabled');
        $textSetting    = $this->settingRepository->findByKey('announcement_text');

        return [
            'enabled' => $enabledSetting?->value() === '1',
            'text'    => $textSetting?->value() ?? '',
        ];
    }
}
