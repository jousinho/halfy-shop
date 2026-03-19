<?php

declare(strict_types=1);

namespace App\Domain\Artwork\Repository;

use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\ValueObject\ArtworkId;
use App\Domain\Category\ValueObject\CategoryId;

interface ArtworkRepository
{
    public function save(Artwork $artwork): void;

    public function delete(Artwork $artwork): void;

    public function findById(ArtworkId $id): ?Artwork;

    /** @return Artwork[] */
    public function findAll(): array;

    /** @return Artwork[] */
    public function findByCategory(CategoryId $id): array;

    public function findNextSortOrder(): int;

    public function findByShopUrl(string $shopUrl): ?Artwork;
}
