<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Blog\ValueObject;

use App\Domain\Blog\ValueObject\PostSlug;
use PHPUnit\Framework\TestCase;

final class PostSlugTest extends TestCase
{
    public function test_create_slug__when_valid__should_return_instance(): void
    {
        $slug = PostSlug::create('mi-primer-post');

        $this->assertSame('mi-primer-post', $slug->value());
    }

    public function test_create_slug__when_empty__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PostSlug::create('');
    }

    public function test_create_slug__when_contains_uppercase__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PostSlug::create('Mi-Post');
    }

    public function test_create_slug__when_contains_spaces__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PostSlug::create('mi post');
    }

    public function test_create_slug__when_starts_with_hyphen__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PostSlug::create('-mi-post');
    }

    public function test_create_slug__when_ends_with_hyphen__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PostSlug::create('mi-post-');
    }
}
