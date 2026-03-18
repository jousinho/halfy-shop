<?php

declare(strict_types=1);

namespace App\Application\Category\Create;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Category\ValueObject\CategoryName;
use App\Domain\Category\ValueObject\CategorySlug;

final class CreateCategoryService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {}

    public function execute(CreateCategoryCommand $command): void
    {
        $category = $this->buildCategory($command);
        $this->save($category);
    }

    private function buildCategory(CreateCategoryCommand $command): Category
    {
        return Category::create(
            id:        CategoryId::generate(),
            name:      CategoryName::create($command->name),
            slug:      CategorySlug::create($command->slug),
            sortOrder: $command->sortOrder,
        );
    }

    private function save(Category $category): void
    {
        $this->categoryRepository->save($category);
    }
}
