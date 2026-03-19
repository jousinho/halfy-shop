<?php

declare(strict_types=1);

namespace App\Domain\Artwork\ValueObject;

final class Price
{
    private function __construct(private readonly float $value) {}

    public static function create(float $value): self
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Price cannot be negative.');
        }

        if (round($value, 2) !== $value) {
            throw new \InvalidArgumentException('Price cannot have more than 2 decimal places.');
        }

        return new self($value);
    }

    public function value(): float
    {
        return $this->value;
    }
}
