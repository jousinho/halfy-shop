<?php

declare(strict_types=1);

namespace App\Application\Setting\GetSiteContactImage;

use App\Domain\Setting\Repository\SettingRepository;

final class GetSiteContactImageService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(): ?string
    {
        return $this->settingRepository->findByKey('site_contact_image')?->value();
    }
}
