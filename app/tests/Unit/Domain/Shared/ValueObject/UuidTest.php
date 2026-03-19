<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shared\ValueObject;

use App\Domain\Shared\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class ConcreteUuid extends Uuid {}

final class UuidTest extends TestCase
{
    public function test_create_uuid__when_value_is_valid__should_return_instance(): void
    {
        $uuid = ConcreteUuid::create('550e8400-e29b-41d4-a716-446655440000');

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $uuid->value());
    }

    public function test_create_uuid__when_value_is_invalid__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ConcreteUuid::create('not-a-valid-uuid');
    }

    public function test_generate_uuid__should_return_valid_uuid(): void
    {
        $uuid = ConcreteUuid::generate();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid->value()
        );
    }

    public function test_equals__when_same_value__should_return_true(): void
    {
        $uuid1 = ConcreteUuid::create('550e8400-e29b-41d4-a716-446655440000');
        $uuid2 = ConcreteUuid::create('550e8400-e29b-41d4-a716-446655440000');

        $this->assertTrue($uuid1->equals($uuid2));
    }

    public function test_equals__when_different_value__should_return_false(): void
    {
        $uuid1 = ConcreteUuid::generate();
        $uuid2 = ConcreteUuid::generate();

        $this->assertFalse($uuid1->equals($uuid2));
    }
}
