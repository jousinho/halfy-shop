<?php

declare(strict_types=1);

namespace App\Domain\Sync\Repository;

use App\Domain\Sync\Entity\SyncLog;

interface SyncLogRepository
{
    public function save(SyncLog $log): void;

    public function findLatest(): ?SyncLog;
}
