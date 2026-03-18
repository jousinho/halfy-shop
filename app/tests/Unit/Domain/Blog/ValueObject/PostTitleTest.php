<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Blog\ValueObject;

use App\Domain\Blog\ValueObject\PostTitle;
use PHPUnit\Framework\TestCase;

final class PostTitleTest extends TestCase
{
    public function test_create_title__when_valid__should_return_instance(): void
    {
        $title = PostTitle::create('Mi primer post');

        $this->assertSame('Mi primer post', $title->value());
    }

    public function test_create_title__when_empty__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PostTitle::create('');
    }

    public function test_create_title__when_only_spaces__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PostTitle::create('   ');
    }

    public function test_create_title__when_exceeds_max_length__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PostTitle::create(str_repeat('a', 256));
    }

    public function test_create_title__should_trim_whitespace(): void
    {
        $title = PostTitle::create('  Mi post  ');

        $this->assertSame('Mi post', $title->value());
    }
}
