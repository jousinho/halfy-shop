<?php

declare(strict_types=1);

namespace App\Infrastructure\Image;

use App\Application\Shared\ImageProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class GdImageProcessor implements ImageProcessor
{
    private const MAX_WARNING_SIZE_BYTES = 10 * 1024 * 1024;
    private const THUMBNAIL_WIDTH       = 600;
    private const LIGHTBOX_MAX_WIDTH    = 1400;
    private const JPEG_QUALITY          = 85;

    public function __construct(
        private readonly string $uploadsDir,
        private readonly LoggerInterface $logger,
    ) {}

    public function process(UploadedFile $file, string $destinationDir): string
    {
        $this->warnIfFileTooLarge($file);
        $source   = $this->loadImage($file);
        $filename = $this->generateFilename();
        $this->saveThumbnail($source, $destinationDir, $filename);
        $this->saveLightbox($source, $destinationDir, $filename);
        imagedestroy($source);

        return $filename;
    }

    private function warnIfFileTooLarge(UploadedFile $file): void
    {
        if ($file->getSize() > self::MAX_WARNING_SIZE_BYTES) {
            $this->logger->warning('Uploaded image exceeds 10MB warning threshold', [
                'filename' => $file->getClientOriginalName(),
                'size'     => $file->getSize(),
            ]);
        }
    }

    private function loadImage(UploadedFile $file): \GdImage
    {
        $path = $file->getPathname();
        $mime = $file->getMimeType();

        $image = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png'  => imagecreatefrompng($path),
            'image/gif'  => imagecreatefromgif($path),
            'image/webp' => imagecreatefromwebp($path),
            default      => throw new \InvalidArgumentException(sprintf('Unsupported image type: %s', $mime)),
        };

        if ($image === false) {
            throw new \RuntimeException(sprintf('Failed to load image: %s', $file->getClientOriginalName()));
        }

        return $image;
    }

    private function generateFilename(): string
    {
        return uniqid('img_', true) . '.jpg';
    }

    private function saveThumbnail(\GdImage $source, string $destinationDir, string $filename): void
    {
        $dir = $this->uploadsDir . '/' . $destinationDir . '/thumbnails';
        $this->ensureDirectoryExists($dir);

        $thumbnail = $this->scaleToWidth($source, self::THUMBNAIL_WIDTH);
        imagejpeg($thumbnail, $dir . '/' . $filename, self::JPEG_QUALITY);
        imagedestroy($thumbnail);
    }

    private function saveLightbox(\GdImage $source, string $destinationDir, string $filename): void
    {
        $dir = $this->uploadsDir . '/' . $destinationDir;
        $this->ensureDirectoryExists($dir);

        $lightbox = $this->scaleToWidth($source, self::LIGHTBOX_MAX_WIDTH);
        imagejpeg($lightbox, $dir . '/' . $filename, self::JPEG_QUALITY);
        imagedestroy($lightbox);
    }

    private function cropToSize(\GdImage $source, int $targetWidth, int $targetHeight): \GdImage
    {
        $srcWidth  = imagesx($source);
        $srcHeight = imagesy($source);

        $scaleX = $targetWidth  / $srcWidth;
        $scaleY = $targetHeight / $srcHeight;
        $scale  = max($scaleX, $scaleY);

        $scaledWidth  = (int) round($srcWidth  * $scale);
        $scaledHeight = (int) round($srcHeight * $scale);

        $offsetX = (int) round(($scaledWidth  - $targetWidth)  / 2);
        $offsetY = (int) round(($scaledHeight - $targetHeight) / 2);

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled(
            $canvas, $source,
            0, 0,
            (int) round($offsetX / $scale), (int) round($offsetY / $scale),
            $targetWidth, $targetHeight,
            (int) round($targetWidth  / $scale), (int) round($targetHeight / $scale),
        );

        return $canvas;
    }

    private function scaleToWidth(\GdImage $source, int $maxWidth): \GdImage
    {
        $srcWidth  = imagesx($source);
        $srcHeight = imagesy($source);

        if ($srcWidth <= $maxWidth) {
            $copy = imagecreatetruecolor($srcWidth, $srcHeight);
            imagecopy($copy, $source, 0, 0, 0, 0, $srcWidth, $srcHeight);

            return $copy;
        }

        $ratio     = $maxWidth / $srcWidth;
        $newHeight = (int) round($srcHeight * $ratio);

        $lightbox = imagecreatetruecolor($maxWidth, $newHeight);
        imagecopyresampled($lightbox, $source, 0, 0, 0, 0, $maxWidth, $newHeight, $srcWidth, $srcHeight);

        return $lightbox;
    }

    public function delete(string $filename, string $destinationDir): void
    {
        $paths = [
            $this->uploadsDir . '/' . $destinationDir . '/' . $filename,
            $this->uploadsDir . '/' . $destinationDir . '/thumbnails/' . $filename,
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    private function ensureDirectoryExists(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
