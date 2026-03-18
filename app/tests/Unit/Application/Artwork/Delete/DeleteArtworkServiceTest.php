<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Artwork\Delete;

use App\Application\Artwork\Delete\DeleteArtworkCommand;
use App\Application\Artwork\Delete\DeleteArtworkService;
use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Event\ArtworkDeleted;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Artwork\ValueObject\ArtworkId;
use App\Domain\Artwork\ValueObject\ArtworkTitle;
use App\Domain\Artwork\ValueObject\ArtworkYear;
use App\Domain\Artwork\ValueObject\Dimensions;
use App\Domain\Artwork\ValueObject\Technique;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class DeleteArtworkServiceTest extends TestCase
{
    private ArtworkRepository&MockObject $artworkRepository;
    private EventDispatcherInterface&MockObject $dispatcher;
    private string $uploadsDir;

    protected function setUp(): void
    {
        $this->artworkRepository = $this->createMock(ArtworkRepository::class);
        $this->dispatcher        = $this->createMock(EventDispatcherInterface::class);
        $this->uploadsDir        = sys_get_temp_dir();
    }

    public function test_execute__should_call_delete_on_repository(): void
    {
        $artwork = $this->buildArtwork();
        $artwork->pullDomainEvents();

        $this->artworkRepository->method('findById')->willReturn($artwork);
        $this->dispatcher->method('dispatch');

        $this->artworkRepository
            ->expects($this->once())
            ->method('delete')
            ->with($artwork);

        $this->buildService()->execute(DeleteArtworkCommand::create($artwork->id()->value()));
    }

    public function test_execute__should_dispatch_deleted_event(): void
    {
        $artwork = $this->buildArtwork();
        $artwork->pullDomainEvents();

        $this->artworkRepository->method('findById')->willReturn($artwork);
        $this->artworkRepository->method('delete');

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ArtworkDeleted::class));

        $this->buildService()->execute(DeleteArtworkCommand::create($artwork->id()->value()));
    }

    public function test_execute__when_artwork_not_found__should_throw_exception(): void
    {
        $this->artworkRepository->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $this->buildService()->execute(DeleteArtworkCommand::create(ArtworkId::generate()->value()));
    }

    public function test_execute__when_image_file_exists_on_disk__should_delete_it(): void
    {
        $filename  = 'test-artwork-' . uniqid() . '.jpg';
        $imageDir  = $this->uploadsDir . '/artworks';

        if (!is_dir($imageDir)) {
            mkdir($imageDir, 0777, true);
        }

        $imagePath = $imageDir . '/' . $filename;
        file_put_contents($imagePath, 'fake image content');

        $artwork = $this->buildArtwork(imageFilename: $filename);
        $artwork->pullDomainEvents();

        $this->artworkRepository->method('findById')->willReturn($artwork);
        $this->artworkRepository->method('delete');
        $this->dispatcher->method('dispatch');

        $this->buildService()->execute(DeleteArtworkCommand::create($artwork->id()->value()));

        $this->assertFileDoesNotExist($imagePath);
    }

    public function test_execute__when_image_file_does_not_exist__should_not_throw(): void
    {
        $artwork = $this->buildArtwork(imageFilename: 'non-existent-file.jpg');
        $artwork->pullDomainEvents();

        $this->artworkRepository->method('findById')->willReturn($artwork);
        $this->artworkRepository->method('delete');
        $this->dispatcher->method('dispatch');

        $this->expectNotToPerformAssertions();

        $this->buildService()->execute(DeleteArtworkCommand::create($artwork->id()->value()));
    }

    private function buildService(): DeleteArtworkService
    {
        return new DeleteArtworkService(
            $this->artworkRepository,
            $this->dispatcher,
            $this->uploadsDir,
        );
    }

    private function buildArtwork(string $imageFilename = 'imagen.jpg'): Artwork
    {
        return Artwork::create(
            id:            ArtworkId::generate(),
            title:         ArtworkTitle::create('Obra a eliminar'),
            description:   null,
            technique:     Technique::create('Óleo'),
            dimensions:    Dimensions::create('50x60'),
            year:          ArtworkYear::create(2023),
            price:         null,
            imageFilename: $imageFilename,
            shopUrl:       null,
            isAvailable:   true,
            sortOrder:     1,
        );
    }
}
