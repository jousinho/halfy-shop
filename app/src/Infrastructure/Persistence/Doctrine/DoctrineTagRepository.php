<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\Repository\TagRepository;
use App\Domain\Tag\ValueObject\TagId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineTagRepository extends ServiceEntityRepository implements TagRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function save(Tag $tag): void
    {
        $this->getEntityManager()->persist($tag);
        $this->getEntityManager()->flush();
    }

    public function delete(Tag $tag): void
    {
        $this->getEntityManager()->remove($tag);
        $this->getEntityManager()->flush();
    }

    public function findById(TagId $id): ?Tag
    {
        return $this->find($id->value());
    }

    public function findBySlug(string $slug): ?Tag
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /** @return Tag[] */
    public function findAll(): array
    {
        return $this->findBy([], ['name' => 'ASC']);
    }
}
