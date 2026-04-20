<?php

declare(strict_types=1);

namespace App\Application\Artwork\Create;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CreateArtworkCommand
{
    private function __construct(
        public readonly string $title,
        public readonly ?string $titleEn,
        public readonly ?string $description,
        public readonly ?string $descriptionEn,
        public readonly string $technique,
        public readonly ?string $techniqueEn,
        public readonly string $dimensions,
        public readonly int $year,
        public readonly ?float $price,
        public readonly UploadedFile $imageFile,
        public readonly ?string $shopUrl,
        public readonly bool $isAvailable,
        public readonly array $categoryIds,
        public readonly array $tagIds,
    ) {}

    public static function create(
        string $title,
        ?string $titleEn,
        ?string $description,
        ?string $descriptionEn,
        string $technique,
        ?string $techniqueEn,
        string $dimensions,
        int $year,
        ?float $price,
        UploadedFile $imageFile,
        ?string $shopUrl,
        bool $isAvailable,
        array $categoryIds,
        array $tagIds,
    ): self {
        return new self(
            $title,
            $titleEn,
            $description,
            $descriptionEn,
            $technique,
            $techniqueEn,
            $dimensions,
            $year,
            $price,
            $imageFile,
            $shopUrl,
            $isAvailable,
            $categoryIds,
            $tagIds,
        );
    }
}
