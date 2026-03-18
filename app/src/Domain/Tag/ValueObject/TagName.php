<?php

declare(strict_types=1);

namespace App\Domain\Tag\ValueObject;

final class TagName
{
    private function __construct(private readonly string $value) {}

    public static function create(string $value): self
    {
        $value = mb_strtolower(trim($value));

        if ($value === '') {
            throw new \InvalidArgumentException('Tag name cannot be empty.');
        }

        if (mb_strlen($value) > 50) {
            throw new \InvalidArgumentException('Tag name cannot exceed 50 characters.');
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
