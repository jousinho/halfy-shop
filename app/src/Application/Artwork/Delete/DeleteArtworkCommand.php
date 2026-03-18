<?php

declare(strict_types=1);

namespace App\Application\Artwork\Delete;

final class DeleteArtworkCommand
{
    private function __construct(
        public readonly string $id,
    ) {}

    public static function create(string $id): self
    {
        return new self($id);
    }
}
