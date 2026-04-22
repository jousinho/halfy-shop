<?php

declare(strict_types=1);

namespace App\Application\Setting\GetSiteLogo;

use App\Domain\Setting\Repository\SettingRepository;

final class GetSiteLogoService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(): ?string
    {
        $setting = $this->settingRepository->findByKey('site_logo');

        return $setting?->value();
    }
}
