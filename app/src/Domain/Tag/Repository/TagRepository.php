<?php

declare(strict_types=1);

namespace App\Domain\Tag\Repository;

use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\ValueObject\TagId;

interface TagRepository
{
    public function save(Tag $tag): void;

    public function delete(Tag $tag): void;

    public function findById(TagId $id): ?Tag;

    public function findBySlug(string $slug): ?Tag;

    /** @return Tag[] */
    public function findAll(): array;
}
