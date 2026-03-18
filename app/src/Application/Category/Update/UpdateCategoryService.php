<?php

declare(strict_types=1);

namespace App\Application\Category\Update;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Category\ValueObject\CategoryName;
use App\Domain\Category\ValueObject\CategorySlug;

final class UpdateCategoryService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {}

    public function execute(UpdateCategoryCommand $command): void
    {
        $category = $this->findCategoryOrFail($command->id);
        $this->updateCategoryData($category, $command);
        $this->save($category);
    }

    private function findCategoryOrFail(string $id): Category
    {
        $category = $this->categoryRepository->findById(CategoryId::create($id));

        if ($category === null) {
            throw new \RuntimeException(sprintf('Category "%s" not found.', $id));
        }

        return $category;
    }

    private function updateCategoryData(Category $category, UpdateCategoryCommand $command): void
    {
        $category->update(
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
