<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Artwork\ValueObject;

use App\Domain\Artwork\ValueObject\Dimensions;
use PHPUnit\Framework\TestCase;

final class DimensionsTest extends TestCase
{
    public function test_create_dimensions__when_valid__should_return_instance(): void
    {
        $dimensions = Dimensions::create('35x37 cm');

        $this->assertSame('35x37 cm', $dimensions->value());
    }

    public function test_create_dimensions__when_empty__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Dimensions::create('');
    }

    public function test_create_dimensions__when_only_spaces__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Dimensions::create('   ');
    }

    public function test_create_dimensions__when_exceeds_max_length__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Dimensions::create(str_repeat('a', 51));
    }

    public function test_create_dimensions__should_trim_whitespace(): void
    {
        $dimensions = Dimensions::create('  20x30 cm  ');

        $this->assertSame('20x30 cm', $dimensions->value());
    }
}
