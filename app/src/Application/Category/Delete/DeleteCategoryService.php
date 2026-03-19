<?php

declare(strict_types=1);

namespace App\Application\Category\Delete;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;

final class DeleteCategoryService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {}

    public function execute(DeleteCategoryCommand $command): void
    {
        $category = $this->findCategoryOrFail($command->id);
        $this->delete($category);
    }

    private function findCategoryOrFail(string $id): Category
    {
        $category = $this->categoryRepository->findById(CategoryId::create($id));

        if ($category === null) {
            throw new \RuntimeException(sprintf('Category "%s" not found.', $id));
        }

        return $category;
    }

    private function delete(Category $category): void
    {
        $this->categoryRepository->delete($category);
    }
}
