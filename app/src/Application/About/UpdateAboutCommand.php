<?php

declare(strict_types=1);

namespace App\Application\About;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UpdateAboutCommand
{
    private function __construct(
        public readonly string $content,
        public readonly ?string $contentEn,
        public readonly ?UploadedFile $photoFile,
        public readonly bool $removePhoto,
    ) {}

    public static function create(
        string $content,
        ?string $contentEn,
        ?UploadedFile $photoFile,
        bool $removePhoto = false,
    ): self {
        return new self($content, $contentEn, $photoFile, $removePhoto);
    }
}
