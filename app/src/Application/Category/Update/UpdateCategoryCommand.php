<?php

declare(strict_types=1);

namespace App\Application\Category\Update;

final class UpdateCategoryCommand
{
    private function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly int $sortOrder,
    ) {}

    public static function create(string $id, string $name, string $slug, int $sortOrder): self
    {
        return new self($id, $name, $slug, $sortOrder);
    }
}
