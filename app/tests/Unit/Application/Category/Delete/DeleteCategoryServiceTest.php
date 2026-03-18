<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Category\Delete;

use App\Application\Category\Delete\DeleteCategoryCommand;
use App\Application\Category\Delete\DeleteCategoryService;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Category\ValueObject\CategoryName;
use App\Domain\Category\ValueObject\CategorySlug;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class DeleteCategoryServiceTest extends TestCase
{
    private CategoryRepository&MockObject $categoryRepository;
    private DeleteCategoryService $service;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->service            = new DeleteCategoryService($this->categoryRepository);
    }

    public function test_execute__should_call_delete_on_repository(): void
    {
        $category = $this->buildCategory();
        $this->categoryRepository->method('findById')->willReturn($category);

        $this->categoryRepository
            ->expects($this->once())
            ->method('delete')
            ->with($category);

        $this->service->execute(DeleteCategoryCommand::create($category->id()->value()));
    }

    public function test_execute__when_category_not_found__should_throw_exception(): void
    {
        $this->categoryRepository->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $this->service->execute(DeleteCategoryCommand::create(CategoryId::generate()->value()));
    }

    private function buildCategory(): Category
    {
        return Category::create(
            CategoryId::generate(),
            CategoryName::create('Ilustración'),
            CategorySlug::create('ilustracion'),
            1,
        );
    }
}
