<?php

declare(strict_types=1);

namespace App\Domain\Blog\ValueObject;

final class PostTitle
{
    private function __construct(private readonly string $value) {}

    public static function create(string $value): self
    {
        $value = trim($value);

        if ($value === '') {
            throw new \InvalidArgumentException('Post title cannot be empty.');
        }

        if (mb_strlen($value) > 255) {
            throw new \InvalidArgumentException('Post title cannot exceed 255 characters.');
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
