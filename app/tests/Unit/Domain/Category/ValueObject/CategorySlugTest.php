<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Category\ValueObject;

use App\Domain\Category\ValueObject\CategorySlug;
use PHPUnit\Framework\TestCase;

final class CategorySlugTest extends TestCase
{
    public function test_create_slug__when_valid__should_return_instance(): void
    {
        $slug = CategorySlug::create('grabados');

        $this->assertSame('grabados', $slug->value());
    }

    public function test_create_slug__when_valid_with_hyphens__should_return_instance(): void
    {
        $slug = CategorySlug::create('obra-original');

        $this->assertSame('obra-original', $slug->value());
    }

    public function test_create_slug__when_empty__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CategorySlug::create('');
    }

    public function test_create_slug__when_contains_uppercase__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CategorySlug::create('Grabados');
    }

    public function test_create_slug__when_contains_spaces__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CategorySlug::create('obra original');
    }

    public function test_create_slug__when_starts_with_hyphen__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CategorySlug::create('-grabados');
    }

    public function test_create_slug__when_ends_with_hyphen__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CategorySlug::create('grabados-');
    }
}
