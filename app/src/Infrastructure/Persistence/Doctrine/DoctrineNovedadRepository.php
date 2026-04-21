<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Novedad\Entity\Novedad;
use App\Domain\Novedad\Repository\NovedadRepository;
use App\Domain\Novedad\ValueObject\NovedadId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineNovedadRepository implements NovedadRepository
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function findById(NovedadId $id): ?Novedad
    {
        return $this->em->find(Novedad::class, $id->value());
    }

    public function findBySlug(string $slug): ?Novedad
    {
        return $this->em->getRepository(Novedad::class)->findOneBy(['slug' => $slug]);
    }

    public function findAllPublished(): array
    {
        return $this->em->getRepository(Novedad::class)->findBy(
            ['publicado' => true],
            ['fecha' => 'DESC'],
        );
    }

    public function findAll(): array
    {
        return $this->em->getRepository(Novedad::class)->findBy([], ['fecha' => 'DESC']);
    }

    public function save(Novedad $novedad): void
    {
        $this->em->persist($novedad);
        $this->em->flush();
    }

    public function delete(Novedad $novedad): void
    {
        $this->em->remove($novedad);
        $this->em->flush();
    }

    public function slugExists(string $slug, ?string $excludeId = null): bool
    {
        $qb = $this->em->createQueryBuilder()
            ->select('COUNT(n.id)')
            ->from(Novedad::class, 'n')
            ->where('n.slug = :slug')
            ->setParameter('slug', $slug);

        if ($excludeId !== null) {
            $qb->andWhere('n.id != :excludeId')->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
