<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Artwork\ValueObject\ArtworkId;
use App\Domain\Category\ValueObject\CategoryId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineArtworkRepository extends ServiceEntityRepository implements ArtworkRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artwork::class);
    }

    public function save(Artwork $artwork): void
    {
        $this->getEntityManager()->persist($artwork);
        $this->getEntityManager()->flush();
    }

    public function delete(Artwork $artwork): void
    {
        $this->getEntityManager()->remove($artwork);
        $this->getEntityManager()->flush();
    }

    public function findById(ArtworkId $id): ?Artwork
    {
        return $this->find($id->value());
    }

    /** @return Artwork[] */
    public function findAll(): array
    {
        return $this->findBy([], ['sortOrder' => 'ASC']);
    }

    public function findAllVisible(): array
    {
        return $this->findBy(['visible' => true], ['sortOrder' => 'ASC']);
    }

    /** @return Artwork[] */
    public function findByCategory(CategoryId $id): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.categories', 'c')
            ->where('c.id = :categoryId')
            ->setParameter('categoryId', $id->value())
            ->orderBy('a.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findVisibleByCategory(CategoryId $id): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.categories', 'c')
            ->where('c.id = :categoryId')
            ->andWhere('a.visible = true')
            ->setParameter('categoryId', $id->value())
            ->orderBy('a.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findNextSortOrder(): int
    {
        $max = $this->createQueryBuilder('a')
            ->select('MAX(a.sortOrder)')
            ->getQuery()
            ->getSingleScalarResult();

        return ($max ?? 0) + 1;
    }

    public function findByShopUrl(string $shopUrl): ?Artwork
    {
        return $this->findOneBy(['shopUrl' => $shopUrl]);
    }

    public function search(string $query): array
    {
        $term = '%' . mb_strtolower($query) . '%';

        return $this->createQueryBuilder('a')
            ->leftJoin('a.categories', 'c')
            ->where('LOWER(a.title) LIKE :term')
            ->orWhere('LOWER(a.titleEn) LIKE :term')
            ->orWhere('LOWER(a.description) LIKE :term')
            ->orWhere('LOWER(a.descriptionEn) LIKE :term')
            ->orWhere('LOWER(a.technique) LIKE :term')
            ->orWhere('LOWER(a.techniqueEn) LIKE :term')
            ->orWhere('LOWER(a.dimensions) LIKE :term')
            ->orWhere('LOWER(c.name) LIKE :term')
            ->setParameter('term', $term)
            ->orderBy('a.sortOrder', 'ASC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }
}
