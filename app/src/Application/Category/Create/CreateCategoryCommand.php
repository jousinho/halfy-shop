<?php

declare(strict_types=1);

namespace App\Application\Category\Create;

final class CreateCategoryCommand
{
    private function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly int $sortOrder,
    ) {}

    public static function create(string $name, string $slug, int $sortOrder): self
    {
        return new self($name, $slug, $sortOrder);
    }
}
