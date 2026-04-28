<?php

declare(strict_types=1);

namespace App\Application\Setting\GetSiteFavicon;

use App\Domain\Setting\Repository\SettingRepository;

final class GetSiteFaviconService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(): ?string
    {
        $setting = $this->settingRepository->findByKey('site_favicon');

        return $setting?->value();
    }
}
