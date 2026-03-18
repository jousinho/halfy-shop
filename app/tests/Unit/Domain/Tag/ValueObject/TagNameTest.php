<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Tag\ValueObject;

use App\Domain\Tag\ValueObject\TagName;
use PHPUnit\Framework\TestCase;

final class TagNameTest extends TestCase
{
    public function test_create_tag_name__when_valid__should_return_instance(): void
    {
        $name = TagName::create('acuarela');

        $this->assertSame('acuarela', $name->value());
    }

    public function test_create_tag_name__should_convert_to_lowercase(): void
    {
        $name = TagName::create('ACUARELA');

        $this->assertSame('acuarela', $name->value());
    }

    public function test_create_tag_name__should_trim_whitespace(): void
    {
        $name = TagName::create('  naturaleza  ');

        $this->assertSame('naturaleza', $name->value());
    }

    public function test_create_tag_name__when_empty__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        TagName::create('');
    }

    public function test_create_tag_name__when_only_spaces__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        TagName::create('   ');
    }

    public function test_create_tag_name__when_exceeds_max_length__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        TagName::create(str_repeat('a', 51));
    }
}
