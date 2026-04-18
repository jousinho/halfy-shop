<?php

declare(strict_types=1);

namespace App\Application\Category\Reorder;

use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;

final class ReorderCategoriesService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {}

    public function execute(ReorderCategoriesCommand $command): void
    {
        foreach ($command->orderedIds as $position => $id) {
            $category = $this->categoryRepository->findById(CategoryId::create($id));
            if ($category !== null) {
                $category->setSortOrder($position + 1);
                $this->categoryRepository->save($category);
            }
        }
    }
}
