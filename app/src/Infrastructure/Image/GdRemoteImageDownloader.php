<?php

declare(strict_types=1);

namespace App\Infrastructure\Image;

use App\Application\Shared\RemoteImageDownloader;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class GdRemoteImageDownloader implements RemoteImageDownloader
{
    public function __construct(
        private readonly GdImageProcessor $imageProcessor,
    ) {}

    public function download(string $url, string $destinationDir): string
    {
        $tempFile = $this->downloadToTempFile($url);

        $uploadedFile = new UploadedFile($tempFile, basename($url), null, null, true);

        try {
            return $this->imageProcessor->process($uploadedFile, $destinationDir);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    private function downloadToTempFile(string $url): string
    {
        $content = @file_get_contents($url);

        if ($content === false) {
            throw new \RuntimeException(sprintf('Failed to download image from: %s', $url));
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'sync_') . '.jpg';
        file_put_contents($tempFile, $content);

        return $tempFile;
    }
}
