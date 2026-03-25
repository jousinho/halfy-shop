<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineSettingRepository extends ServiceEntityRepository implements SettingRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    public function findByKey(string $key): ?Setting
    {
        return $this->find($key);
    }

    public function save(Setting $setting): void
    {
        $this->getEntityManager()->persist($setting);
        $this->getEntityManager()->flush();
    }
}
