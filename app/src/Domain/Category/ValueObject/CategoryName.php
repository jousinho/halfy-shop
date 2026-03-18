<?php

declare(strict_types=1);

namespace App\Domain\Category\ValueObject;

final class CategoryName
{
    private function __construct(private readonly string $value) {}

    public static function create(string $value): self
    {
        $value = trim($value);

        if ($value === '') {
            throw new \InvalidArgumentException('Category name cannot be empty.');
        }

        if (mb_strlen($value) > 100) {
            throw new \InvalidArgumentException('Category name cannot exceed 100 characters.');
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
