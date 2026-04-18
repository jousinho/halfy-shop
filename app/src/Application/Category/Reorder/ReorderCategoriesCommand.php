<?php

declare(strict_types=1);

namespace App\Application\Category\Reorder;

final class ReorderCategoriesCommand
{
    private function __construct(
        public readonly array $orderedIds,
    ) {}

    public static function create(array $orderedIds): self
    {
        return new self($orderedIds);
    }
}
