<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Image;

use App\Infrastructure\Image\GdImageProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class GdImageProcessorTest extends TestCase
{
    private string $uploadsDir;
    private LoggerInterface&MockObject $logger;
    private GdImageProcessor $processor;

    protected function setUp(): void
    {
        $this->uploadsDir = sys_get_temp_dir() . '/gd_test_' . uniqid();
        mkdir($this->uploadsDir);

        $this->logger    = $this->createMock(LoggerInterface::class);
        $this->processor = new GdImageProcessor($this->uploadsDir, $this->logger);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->uploadsDir);
    }

    public function test_process__should_return_a_filename(): void
    {
        $file     = $this->createUploadedFile(800, 600);
        $filename = $this->processor->process($file, 'artworks');

        $this->assertNotEmpty($filename);
        $this->assertStringEndsWith('.jpg', $filename);
    }

    public function test_process__should_create_lightbox_file(): void
    {
        $file     = $this->createUploadedFile(800, 600);
        $filename = $this->processor->process($file, 'artworks');

        $this->assertFileExists($this->uploadsDir . '/artworks/' . $filename);
    }

    public function test_process__should_create_thumbnail_file(): void
    {
        $file     = $this->createUploadedFile(800, 600);
        $filename = $this->processor->process($file, 'artworks');

        $this->assertFileExists($this->uploadsDir . '/artworks/thumbnails/' . $filename);
    }

    public function test_process__thumbnail_should_be_600x400(): void
    {
        $file     = $this->createUploadedFile(1200, 900);
        $filename = $this->processor->process($file, 'artworks');

        $path = $this->uploadsDir . '/artworks/thumbnails/' . $filename;
        [$width, $height] = getimagesize($path);

        $this->assertSame(600, $width);
        $this->assertSame(400, $height);
    }

    public function test_process__lightbox_should_not_exceed_1400px_wide(): void
    {
        $file     = $this->createUploadedFile(2000, 1500);
        $filename = $this->processor->process($file, 'artworks');

        $path = $this->uploadsDir . '/artworks/' . $filename;
        [$width] = getimagesize($path);

        $this->assertLessThanOrEqual(1400, $width);
    }

    public function test_process__lightbox_should_preserve_aspect_ratio(): void
    {
        $file     = $this->createUploadedFile(2000, 1000);
        $filename = $this->processor->process($file, 'artworks');

        $path = $this->uploadsDir . '/artworks/' . $filename;
        [$width, $height] = getimagesize($path);

        $this->assertSame(1400, $width);
        $this->assertSame(700, $height);
    }

    public function test_process__when_image_narrower_than_1400px__should_not_upscale(): void
    {
        $file     = $this->createUploadedFile(800, 600);
        $filename = $this->processor->process($file, 'artworks');

        $path = $this->uploadsDir . '/artworks/' . $filename;
        [$width] = getimagesize($path);

        $this->assertSame(800, $width);
    }

    public function test_process__should_create_destination_directories_if_missing(): void
    {
        $file = $this->createUploadedFile(100, 100);
        $this->processor->process($file, 'new-dir');

        $this->assertDirectoryExists($this->uploadsDir . '/new-dir');
        $this->assertDirectoryExists($this->uploadsDir . '/new-dir/thumbnails');
    }

    public function test_process__should_generate_unique_filenames(): void
    {
        $file1 = $this->createUploadedFile(100, 100);
        $file2 = $this->createUploadedFile(100, 100);

        $filename1 = $this->processor->process($file1, 'artworks');
        $filename2 = $this->processor->process($file2, 'artworks');

        $this->assertNotSame($filename1, $filename2);
    }

    public function test_process__when_file_exceeds_10mb__should_log_warning(): void
    {
        $file = $this->createUploadedFile(100, 100, sizeOverride: 11 * 1024 * 1024);

        $this->logger
            ->expects($this->once())
            ->method('warning');

        $this->processor->process($file, 'artworks');
    }

    public function test_process__when_file_under_10mb__should_not_log_warning(): void
    {
        $file = $this->createUploadedFile(100, 100);

        $this->logger->expects($this->never())->method('warning');

        $this->processor->process($file, 'artworks');
    }

    public function test_process__when_unsupported_mime_type__should_throw_exception(): void
    {
        $path = $this->uploadsDir . '/test.bmp';
        file_put_contents($path, 'fake bmp content');

        $file = $this->createMock(UploadedFile::class);
        $file->method('getSize')->willReturn(100);
        $file->method('getMimeType')->willReturn('image/bmp');
        $file->method('getPathname')->willReturn($path);
        $file->method('getClientOriginalName')->willReturn('test.bmp');

        $this->expectException(\InvalidArgumentException::class);

        $this->processor->process($file, 'artworks');
    }

    private function createUploadedFile(int $width, int $height, int $sizeOverride = 0): UploadedFile
    {
        $path  = $this->uploadsDir . '/source_' . uniqid() . '.jpg';
        $image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($image, 150, 100, 200);
        imagefill($image, 0, 0, $color);
        imagejpeg($image, $path, 90);
        imagedestroy($image);

        if ($sizeOverride > 0) {
            $file = $this->createMock(UploadedFile::class);
            $file->method('getSize')->willReturn($sizeOverride);
            $file->method('getMimeType')->willReturn('image/jpeg');
            $file->method('getPathname')->willReturn($path);
            $file->method('getClientOriginalName')->willReturn('test.jpg');

            return $file;
        }

        return new UploadedFile($path, 'test.jpg', 'image/jpeg', null, true);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $dir . '/' . $entry;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}
