<?php

declare(strict_types=1);

namespace App\Application\Category\Delete;

final class DeleteCategoryCommand
{
    private function __construct(
        public readonly string $id,
    ) {}

    public static function create(string $id): self
    {
        return new self($id);
    }
}
