<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Artwork\ValueObject;

use App\Domain\Artwork\ValueObject\Technique;
use PHPUnit\Framework\TestCase;

final class TechniqueTest extends TestCase
{
    public function test_create_technique__when_valid__should_return_instance(): void
    {
        $technique = Technique::create('Fotopolímero');

        $this->assertSame('Fotopolímero', $technique->value());
    }

    public function test_create_technique__when_empty__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Technique::create('');
    }

    public function test_create_technique__when_only_spaces__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Technique::create('   ');
    }

    public function test_create_technique__when_exceeds_max_length__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Technique::create(str_repeat('a', 101));
    }

    public function test_create_technique__should_trim_whitespace(): void
    {
        $technique = Technique::create('  Acuarela  ');

        $this->assertSame('Acuarela', $technique->value());
    }
}
