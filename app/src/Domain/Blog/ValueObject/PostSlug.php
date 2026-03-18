<?php

declare(strict_types=1);

namespace App\Domain\Blog\ValueObject;

final class PostSlug
{
    private function __construct(private readonly string $value) {}

    public static function create(string $value): self
    {
        $value = trim($value);

        if ($value === '') {
            throw new \InvalidArgumentException('Post slug cannot be empty.');
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
            throw new \InvalidArgumentException('Post slug may only contain lowercase letters, numbers and hyphens.');
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
