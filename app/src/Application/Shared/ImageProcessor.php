<?php

declare(strict_types=1);

namespace App\Application\Shared;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ImageProcessor
{
    public function process(UploadedFile $file, string $destinationDir): string;
}
