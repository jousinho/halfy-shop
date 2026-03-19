<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Category\ValueObject;

use App\Domain\Category\ValueObject\CategoryName;
use PHPUnit\Framework\TestCase;

final class CategoryNameTest extends TestCase
{
    public function test_create_name__when_valid__should_return_instance(): void
    {
        $name = CategoryName::create('Grabados');

        $this->assertSame('Grabados', $name->value());
    }

    public function test_create_name__when_empty__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CategoryName::create('');
    }

    public function test_create_name__when_only_spaces__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CategoryName::create('   ');
    }

    public function test_create_name__when_exceeds_max_length__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CategoryName::create(str_repeat('a', 101));
    }

    public function test_create_name__should_trim_whitespace(): void
    {
        $name = CategoryName::create('  Ilustraciones  ');

        $this->assertSame('Ilustraciones', $name->value());
    }
}
