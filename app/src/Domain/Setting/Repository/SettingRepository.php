<?php

declare(strict_types=1);

namespace App\Domain\Setting\Repository;

use App\Domain\Setting\Entity\Setting;

interface SettingRepository
{
    public function findByKey(string $key): ?Setting;

    public function save(Setting $setting): void;
}
