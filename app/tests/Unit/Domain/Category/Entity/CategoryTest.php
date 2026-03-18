<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Category\Entity;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Category\ValueObject\CategoryName;
use App\Domain\Category\ValueObject\CategorySlug;
use PHPUnit\Framework\TestCase;

final class CategoryTest extends TestCase
{
    public function test_create_category__should_store_all_fields_correctly(): void
    {
        $id       = CategoryId::generate();
        $category = $this->buildCategory($id);

        $this->assertSame($id->value(), $category->id()->value());
        $this->assertSame('Grabados', $category->name()->value());
        $this->assertSame('grabados', $category->slug()->value());
        $this->assertSame(1, $category->sortOrder());
    }

    public function test_update_category__should_change_fields(): void
    {
        $category = $this->buildCategory();

        $category->update(
            CategoryName::create('Ilustraciones'),
            CategorySlug::create('ilustraciones'),
            2,
        );

        $this->assertSame('Ilustraciones', $category->name()->value());
        $this->assertSame('ilustraciones', $category->slug()->value());
        $this->assertSame(2, $category->sortOrder());
    }

    public function test_update_category__should_not_change_id(): void
    {
        $id       = CategoryId::generate();
        $category = $this->buildCategory($id);

        $category->update(
            CategoryName::create('Ilustraciones'),
            CategorySlug::create('ilustraciones'),
            2,
        );

        $this->assertSame($id->value(), $category->id()->value());
    }

    private function buildCategory(?CategoryId $id = null): Category
    {
        return Category::create(
            id:        $id ?? CategoryId::generate(),
            name:      CategoryName::create('Grabados'),
            slug:      CategorySlug::create('grabados'),
            sortOrder: 1,
        );
    }
}
