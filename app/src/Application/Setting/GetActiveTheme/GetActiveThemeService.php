<?php

declare(strict_types=1);

namespace App\Application\Setting\GetActiveTheme;

use App\Domain\Setting\Repository\SettingRepository;
use App\Domain\Setting\ValueObject\Theme;

final class GetActiveThemeService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(): Theme
    {
        $setting = $this->settingRepository->findByKey('active_theme');

        if ($setting === null) {
            return Theme::Default;
        }

        return Theme::tryFrom($setting->value()) ?? Theme::Default;
    }
}
