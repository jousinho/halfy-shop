<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\About\Entity\AboutPage;
use App\Domain\About\Repository\AboutPageRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineAboutPageRepository extends ServiceEntityRepository implements AboutPageRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AboutPage::class);
    }

    public function save(AboutPage $page): void
    {
        $this->getEntityManager()->persist($page);
        $this->getEntityManager()->flush();
    }

    public function findPage(): ?AboutPage
    {
        return $this->findOneBy([]);
    }
}
