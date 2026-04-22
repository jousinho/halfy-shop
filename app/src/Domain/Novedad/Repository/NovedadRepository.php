<?php

declare(strict_types=1);

namespace App\Domain\Novedad\Repository;

use App\Domain\Novedad\Entity\Novedad;
use App\Domain\Novedad\ValueObject\NovedadId;

interface NovedadRepository
{
    public function findById(NovedadId $id): ?Novedad;

    public function findBySlug(string $slug): ?Novedad;

    /** @return Novedad[] */
    public function findAllPublished(): array;

    /** @return Novedad[] */
    public function findAll(): array;

    public function save(Novedad $novedad): void;

    public function delete(Novedad $novedad): void;

    public function slugExists(string $slug, ?string $excludeId = null): bool;

    /** @return Novedad[] */
    public function search(string $query): array;
}
