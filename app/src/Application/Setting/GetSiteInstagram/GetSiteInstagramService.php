<?php

declare(strict_types=1);

namespace App\Application\Setting\GetSiteInstagram;

use App\Domain\Setting\Repository\SettingRepository;

final class GetSiteInstagramService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(): ?string
    {
        $setting = $this->settingRepository->findByKey('site_instagram');

        return $setting?->value();
    }
}
