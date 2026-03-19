<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Category\ValueObject\CategorySlug;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineCategoryRepository extends ServiceEntityRepository implements CategoryRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function save(Category $category): void
    {
        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();
    }

    public function delete(Category $category): void
    {
        $this->getEntityManager()->remove($category);
        $this->getEntityManager()->flush();
    }

    public function findById(CategoryId $id): ?Category
    {
        return $this->find($id->value());
    }

    /** @return Category[] */
    public function findAll(): array
    {
        return $this->findBy([], ['sortOrder' => 'ASC']);
    }

    public function findBySlug(CategorySlug $slug): ?Category
    {
        return $this->findOneBy(['slug' => $slug->value()]);
    }
}
