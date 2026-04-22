<?php

declare(strict_types=1);

namespace App\Application\Search;

final class SearchResultItem
{
    private function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $subtitle,
        public readonly string $url,
        public readonly ?string $imageFilename,
        public readonly string $imageDir,
    ) {}

    public static function fromArtwork(string $title, ?string $subtitle, string $url, string $imageFilename): self
    {
        return new self('artwork', $title, $subtitle, $url, $imageFilename, 'artworks/thumbnails');
    }

    public static function fromNovedad(string $title, ?string $subtitle, string $url, ?string $imageFilename): self
    {
        return new self('novedad', $title, $subtitle, $url, $imageFilename, 'novedades');
    }
}
