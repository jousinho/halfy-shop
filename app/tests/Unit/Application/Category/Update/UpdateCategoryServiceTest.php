<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Category\Update;

use App\Application\Category\Update\UpdateCategoryCommand;
use App\Application\Category\Update\UpdateCategoryService;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Category\ValueObject\CategoryName;
use App\Domain\Category\ValueObject\CategorySlug;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class UpdateCategoryServiceTest extends TestCase
{
    private CategoryRepository&MockObject $categoryRepository;
    private UpdateCategoryService $service;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->service            = new UpdateCategoryService($this->categoryRepository);
    }

    public function test_execute__should_update_category_fields(): void
    {
        $category = $this->buildCategory();
        $this->categoryRepository->method('findById')->willReturn($category);
        $this->categoryRepository->method('save');

        $this->service->execute(UpdateCategoryCommand::create(
            $category->id()->value(), 'Nuevo Nombre', 'nuevo-nombre', 5,
        ));

        $this->assertSame('Nuevo Nombre', $category->name()->value());
        $this->assertSame('nuevo-nombre', $category->slug()->value());
        $this->assertSame(5, $category->sortOrder());
    }

    public function test_execute__should_save_after_update(): void
    {
        $category = $this->buildCategory();
        $this->categoryRepository->method('findById')->willReturn($category);

        $this->categoryRepository->expects($this->once())->method('save')->with($category);

        $this->service->execute(UpdateCategoryCommand::create(
            $category->id()->value(), 'Nombre', 'nombre', 1,
        ));
    }

    public function test_execute__when_category_not_found__should_throw_exception(): void
    {
        $this->categoryRepository->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $this->service->execute(UpdateCategoryCommand::create(
            CategoryId::generate()->value(), 'Nombre', 'nombre', 1,
        ));
    }

    public function test_execute__should_not_change_id(): void
    {
        $category = $this->buildCategory();
        $originalId = $category->id()->value();

        $this->categoryRepository->method('findById')->willReturn($category);
        $this->categoryRepository->method('save');

        $this->service->execute(UpdateCategoryCommand::create(
            $originalId, 'Otro Nombre', 'otro-nombre', 2,
        ));

        $this->assertSame($originalId, $category->id()->value());
    }

    private function buildCategory(): Category
    {
        return Category::create(
            CategoryId::generate(),
            CategoryName::create('Original'),
            CategorySlug::create('original'),
            1,
        );
    }
}
