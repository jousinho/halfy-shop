<?php

declare(strict_types=1);

namespace App\Application\Search;

final class SearchQuery
{
    private function __construct(public readonly string $term) {}

    public static function create(string $term): self
    {
        return new self(trim($term));
    }

    public function isEmpty(): bool
    {
        return $this->term === '';
    }
}
