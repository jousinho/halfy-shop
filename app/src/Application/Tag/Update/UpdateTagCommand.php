<?php

declare(strict_types=1);

namespace App\Application\Tag\Update;

final class UpdateTagCommand
{
    private function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
    ) {}

    public static function create(string $id, string $name, string $slug): self
    {
        return new self($id, $name, $slug);
    }
}
