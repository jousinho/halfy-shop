<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\About;

use App\Application\About\UpdateAboutCommand;
use App\Application\About\UpdateAboutService;
use App\Application\Shared\ImageProcessor;
use App\Domain\About\Entity\AboutPage;
use App\Domain\About\Repository\AboutPageRepository;
use App\Domain\About\ValueObject\AboutPageId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UpdateAboutServiceTest extends TestCase
{
    private AboutPageRepository&MockObject $aboutPageRepository;
    private ImageProcessor&MockObject $imageProcessor;
    private UpdateAboutService $service;

    protected function setUp(): void
    {
        $this->aboutPageRepository = $this->createMock(AboutPageRepository::class);
        $this->imageProcessor      = $this->createMock(ImageProcessor::class);

        $this->service = new UpdateAboutService(
            $this->aboutPageRepository,
            $this->imageProcessor,
        );
    }

    public function test_execute__should_update_content_and_save(): void
    {
        $page = $this->buildAboutPage();
        $this->aboutPageRepository->method('findPage')->willReturn($page);

        $this->aboutPageRepository->expects($this->once())->method('save')->with($page);

        $this->service->execute(UpdateAboutCommand::create('<p>Nuevo contenido</p>', null));

        $this->assertSame('<p>Nuevo contenido</p>', $page->content());
    }

    public function test_execute__when_page_does_not_exist__should_create_it(): void
    {
        $this->aboutPageRepository->method('findPage')->willReturn(null);

        $capturedPage = null;
        $this->aboutPageRepository
            ->method('save')
            ->willReturnCallback(function (AboutPage $page) use (&$capturedPage): void {
                $capturedPage = $page;
            });

        $this->service->execute(UpdateAboutCommand::create('Contenido inicial', null));

        $this->assertNotNull($capturedPage);
        $this->assertSame('Contenido inicial', $capturedPage->content());
    }

    public function test_execute__when_photo_file_provided__should_process_and_update_photo(): void
    {
        $page      = $this->buildAboutPage(photoFilename: 'old-photo.jpg');
        $photoFile = $this->createMock(UploadedFile::class);

        $this->aboutPageRepository->method('findPage')->willReturn($page);
        $this->aboutPageRepository->method('save');

        $this->imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with($photoFile, 'about')
            ->willReturn('new-photo.jpg');

        $this->service->execute(UpdateAboutCommand::create('Contenido', $photoFile));

        $this->assertSame('new-photo.jpg', $page->photoFilename());
    }

    public function test_execute__when_no_photo_provided__should_keep_existing_photo(): void
    {
        $page = $this->buildAboutPage(photoFilename: 'existing-photo.jpg');

        $this->aboutPageRepository->method('findPage')->willReturn($page);
        $this->aboutPageRepository->method('save');
        $this->imageProcessor->expects($this->never())->method('process');

        $this->service->execute(UpdateAboutCommand::create('Contenido', null));

        $this->assertSame('existing-photo.jpg', $page->photoFilename());
    }

    public function test_execute__when_remove_photo_true__should_clear_photo(): void
    {
        $page = $this->buildAboutPage(photoFilename: 'foto.jpg');

        $this->aboutPageRepository->method('findPage')->willReturn($page);
        $this->aboutPageRepository->method('save');
        $this->imageProcessor->expects($this->never())->method('process');

        $this->service->execute(UpdateAboutCommand::create('Contenido', null, removePhoto: true));

        $this->assertNull($page->photoFilename());
    }

    public function test_execute__when_photo_file_provided_with_remove_true__should_use_new_photo(): void
    {
        $page      = $this->buildAboutPage(photoFilename: 'vieja.jpg');
        $photoFile = $this->createMock(UploadedFile::class);

        $this->aboutPageRepository->method('findPage')->willReturn($page);
        $this->aboutPageRepository->method('save');

        $this->imageProcessor->method('process')->willReturn('nueva.jpg');

        $this->service->execute(UpdateAboutCommand::create('Contenido', $photoFile, removePhoto: true));

        $this->assertSame('nueva.jpg', $page->photoFilename());
    }

    private function buildAboutPage(?string $photoFilename = null): AboutPage
    {
        return AboutPage::create(
            id:            AboutPageId::generate(),
            content:       '<p>Contenido original</p>',
            photoFilename: $photoFilename,
        );
    }
}
