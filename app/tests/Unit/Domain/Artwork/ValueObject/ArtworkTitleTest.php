<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Artwork\ValueObject;

use App\Domain\Artwork\ValueObject\ArtworkTitle;
use PHPUnit\Framework\TestCase;

final class ArtworkTitleTest extends TestCase
{
    public function test_create_title__when_valid__should_return_instance(): void
    {
        $title = ArtworkTitle::create('Podría volar');

        $this->assertSame('Podría volar', $title->value());
    }

    public function test_create_title__when_empty__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ArtworkTitle::create('');
    }

    public function test_create_title__when_only_spaces__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ArtworkTitle::create('   ');
    }

    public function test_create_title__when_exceeds_max_length__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ArtworkTitle::create(str_repeat('a', 256));
    }

    public function test_create_title__should_trim_whitespace(): void
    {
        $title = ArtworkTitle::create('  Podría volar  ');

        $this->assertSame('Podría volar', $title->value());
    }
}
