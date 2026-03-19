<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Sync\Entity\SyncLog;
use App\Domain\Sync\Repository\SyncLogRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineSyncLogRepository extends ServiceEntityRepository implements SyncLogRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SyncLog::class);
    }

    public function save(SyncLog $log): void
    {
        $this->getEntityManager()->persist($log);
        $this->getEntityManager()->flush();
    }

    public function findLatest(): ?SyncLog
    {
        return $this->findOneBy([], ['executedAt' => 'DESC']);
    }
}
