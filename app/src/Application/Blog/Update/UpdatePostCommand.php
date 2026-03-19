<?php

declare(strict_types=1);

namespace App\Application\Blog\Update;

final class UpdatePostCommand
{
    private function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly string $content,
        public readonly ?\DateTimeImmutable $publishedAt,
    ) {}

    public static function create(
        string $id,
        string $title,
        string $slug,
        string $content,
        ?\DateTimeImmutable $publishedAt,
    ): self {
        return new self($id, $title, $slug, $content, $publishedAt);
    }
}
