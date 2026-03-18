<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Category\ValueObject\CategoryName;
use App\Domain\Category\ValueObject\CategorySlug;
use App\Tests\Integration\IntegrationTestCase;

final class DoctrineCategoryRepositoryTest extends IntegrationTestCase
{
    private CategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getService(CategoryRepository::class);
    }

    public function test_save_and_findById__should_persist_and_retrieve(): void
    {
        $category = $this->buildCategory('Grabado', 'grabado');
        $this->repository->save($category);

        $found = $this->repository->findById($category->id());

        $this->assertNotNull($found);
        $this->assertSame('Grabado', $found->name()->value());
        $this->assertSame('grabado', $found->slug()->value());
    }

    public function test_findById__when_not_exists__should_return_null(): void
    {
        $this->assertNull($this->repository->findById(CategoryId::generate()));
    }

    public function test_findBySlug__should_return_matching_category(): void
    {
        $category = $this->buildCategory('Ilustración', 'ilustracion');
        $this->repository->save($category);

        $slug  = CategorySlug::create('ilustracion');
        $found = $this->repository->findBySlug($slug);

        $this->assertNotNull($found);
        $this->assertSame($category->id()->value(), $found->id()->value());
    }

    public function test_findBySlug__when_not_exists__should_return_null(): void
    {
        $slug = CategorySlug::create('no-existe');

        $this->assertNull($this->repository->findBySlug($slug));
    }

    public function test_findAll__should_return_sorted_by_sort_order(): void
    {
        $this->repository->save($this->buildCategory('Z', 'z-cat', sortOrder: 10));
        $this->repository->save($this->buildCategory('A', 'a-cat', sortOrder: 1));

        $all = $this->repository->findAll();

        $sortOrders = array_map(fn ($c) => $c->sortOrder(), $all);
        $this->assertSame($sortOrders, array_values(array_filter(
            array: $sortOrders,
            callback: fn () => true,
        )));
        // Assert ascending order
        for ($i = 1; $i < count($sortOrders); $i++) {
            $this->assertGreaterThanOrEqual($sortOrders[$i - 1], $sortOrders[$i]);
        }
    }

    public function test_delete__should_remove_category(): void
    {
        $category = $this->buildCategory('Borrar', 'borrar');
        $this->repository->save($category);
        $this->repository->delete($category);

        $this->assertNull($this->repository->findById($category->id()));
    }

    private function buildCategory(string $name, string $slug, int $sortOrder = 1): Category
    {
        return Category::create(
            CategoryId::generate(),
            CategoryName::create($name),
            CategorySlug::create($slug),
            $sortOrder,
        );
    }
}
