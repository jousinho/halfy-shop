<?php

declare(strict_types=1);

namespace App\Application\Setting\GetSiteContactEmail;

use App\Domain\Setting\Repository\SettingRepository;

final class GetSiteContactEmailService
{
    public function __construct(private readonly SettingRepository $settingRepository) {}

    public function execute(): ?string
    {
        return $this->settingRepository->findByKey('site_contact_email')?->value();
    }
}
