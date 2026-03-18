<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Artwork\ValueObject;

use App\Domain\Artwork\ValueObject\Price;
use PHPUnit\Framework\TestCase;

final class PriceTest extends TestCase
{
    public function test_create_price__when_valid__should_return_instance(): void
    {
        $price = Price::create(45.00);

        $this->assertSame(45.0, $price->value());
    }

    public function test_create_price__when_zero__should_return_instance(): void
    {
        $price = Price::create(0.0);

        $this->assertSame(0.0, $price->value());
    }

    public function test_create_price__when_negative__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Price::create(-1.0);
    }

    public function test_create_price__when_more_than_two_decimals__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Price::create(45.123);
    }
}
