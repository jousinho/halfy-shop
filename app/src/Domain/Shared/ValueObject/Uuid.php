<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use Ramsey\Uuid\Uuid as RamseyUuid;

abstract class Uuid
{
    private function __construct(private readonly string $value) {}

    public static function create(string $value): static
    {
        if (!RamseyUuid::isValid($value)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid UUID.', $value));
        }

        return new static($value);
    }

    public static function generate(): static
    {
        return new static(RamseyUuid::uuid4()->toString());
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
