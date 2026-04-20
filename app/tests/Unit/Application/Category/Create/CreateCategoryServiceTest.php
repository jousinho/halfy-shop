<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Category\Create;

use App\Application\Category\Create\CreateCategoryCommand;
use App\Application\Category\Create\CreateCategoryService;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class CreateCategoryServiceTest extends TestCase
{
    private CategoryRepository&MockObject $categoryRepository;
    private CreateCategoryService $service;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->service            = new CreateCategoryService($this->categoryRepository);
    }

    public function test_execute__should_save_category(): void
    {
        $this->categoryRepository->expects($this->once())->method('save');

        $this->service->execute(CreateCategoryCommand::create('Ilustración', null, 'ilustracion'));
    }

    public function test_execute__should_save_category_with_correct_data(): void
    {
        $capturedCategory = null;
        $this->categoryRepository
            ->method('save')
            ->willReturnCallback(function (Category $category) use (&$capturedCategory): void {
                $capturedCategory = $category;
            });

        $this->service->execute(CreateCategoryCommand::create('Grabado', null, 'grabado'));

        $this->assertSame('Grabado', $capturedCategory->name()->value());
        $this->assertSame('grabado', $capturedCategory->slug()->value());
        $this->assertSame(1, $capturedCategory->sortOrder());
    }

    public function test_execute__should_generate_a_new_id_each_time(): void
    {
        $ids = [];
        $this->categoryRepository
            ->method('save')
            ->willReturnCallback(function (Category $category) use (&$ids): void {
                $ids[] = $category->id()->value();
            });

        $this->service->execute(CreateCategoryCommand::create('Cat A', null, 'cat-a'));
        $this->service->execute(CreateCategoryCommand::create('Cat B', null, 'cat-b'));

        $this->assertNotSame($ids[0], $ids[1]);
    }

    public function test_execute__when_name_is_empty__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->execute(CreateCategoryCommand::create('', null, 'slug'));
    }

    public function test_execute__when_slug_is_invalid__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->execute(CreateCategoryCommand::create('Nombre', null, 'Slug Con Mayúsculas!'));
    }
}
