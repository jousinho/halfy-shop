<?php

declare(strict_types=1);

namespace App\Domain\Category\Repository;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Category\ValueObject\CategorySlug;

interface CategoryRepository
{
    public function save(Category $category): void;

    public function delete(Category $category): void;

    public function findById(CategoryId $id): ?Category;

    public function findBySlug(CategorySlug $slug): ?Category;

    /** @return Category[] */
    public function findAll(): array;
}
