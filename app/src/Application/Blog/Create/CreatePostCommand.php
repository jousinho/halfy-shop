<?php

declare(strict_types=1);

namespace App\Application\Blog\Create;

final class CreatePostCommand
{
    private function __construct(
        public readonly string $title,
        public readonly string $slug,
        public readonly string $content,
        public readonly ?\DateTimeImmutable $publishedAt,
    ) {}

    public static function create(
        string $title,
        string $slug,
        string $content,
        ?\DateTimeImmutable $publishedAt,
    ): self {
        return new self($title, $slug, $content, $publishedAt);
    }
}
