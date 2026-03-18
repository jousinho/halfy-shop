<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Artwork\Update;

use App\Application\Artwork\Update\UpdateArtworkCommand;
use App\Application\Artwork\Update\UpdateArtworkService;
use App\Application\Shared\ImageProcessor;
use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Event\ArtworkUpdated;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Artwork\ValueObject\ArtworkId;
use App\Domain\Artwork\ValueObject\ArtworkTitle;
use App\Domain\Artwork\ValueObject\ArtworkYear;
use App\Domain\Artwork\ValueObject\Dimensions;
use App\Domain\Artwork\ValueObject\Price;
use App\Domain\Artwork\ValueObject\Technique;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Category\ValueObject\CategoryName;
use App\Domain\Category\ValueObject\CategorySlug;
use App\Domain\Tag\Repository\TagRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class UpdateArtworkServiceTest extends TestCase
{
    private ArtworkRepository&MockObject $artworkRepository;
    private CategoryRepository&MockObject $categoryRepository;
    private TagRepository&MockObject $tagRepository;
    private ImageProcessor&MockObject $imageProcessor;
    private EventDispatcherInterface&MockObject $dispatcher;
    private UpdateArtworkService $service;

    protected function setUp(): void
    {
        $this->artworkRepository  = $this->createMock(ArtworkRepository::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->tagRepository      = $this->createMock(TagRepository::class);
        $this->imageProcessor     = $this->createMock(ImageProcessor::class);
        $this->dispatcher         = $this->createMock(EventDispatcherInterface::class);

        $this->service = new UpdateArtworkService(
            $this->artworkRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->imageProcessor,
            $this->dispatcher,
        );
    }

    public function test_execute__should_update_artwork_fields(): void
    {
        $artwork = $this->buildArtwork();
        $artwork->pullDomainEvents();

        $this->artworkRepository->method('findById')->willReturn($artwork);
        $this->artworkRepository->method('save');
        $this->dispatcher->method('dispatch');

        $this->service->execute($this->buildCommand(
            id:        $artwork->id()->value(),
            title:     'Nuevo título',
            technique: 'Acrílico',
            year:      2025,
            price:     250.00,
        ));

        $this->assertSame('Nuevo título', $artwork->title()->value());
        $this->assertSame('Acrílico', $artwork->technique()->value());
        $this->assertSame(2025, $artwork->year()->value());
        $this->assertSame(250.00, $artwork->price()->value());
    }

    public function test_execute__should_save_and_dispatch_updated_event(): void
    {
        $artwork = $this->buildArtwork();
        $artwork->pullDomainEvents();

        $this->artworkRepository->method('findById')->willReturn($artwork);

        $this->artworkRepository->expects($this->once())->method('save');
        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ArtworkUpdated::class));

        $this->service->execute($this->buildCommand(id: $artwork->id()->value()));
    }

    public function test_execute__when_artwork_not_found__should_throw_exception(): void
    {
        $this->artworkRepository->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $this->service->execute($this->buildCommand(id: ArtworkId::generate()->value()));
    }

    public function test_execute__when_image_file_provided__should_update_image(): void
    {
        $artwork   = $this->buildArtwork();
        $artwork->pullDomainEvents();
        $imageFile = $this->createMock(UploadedFile::class);

        $this->artworkRepository->method('findById')->willReturn($artwork);
        $this->artworkRepository->method('save');
        $this->dispatcher->method('dispatch');

        $this->imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with($imageFile, 'artworks')
            ->willReturn('new-image.jpg');

        $this->service->execute($this->buildCommand(id: $artwork->id()->value(), imageFile: $imageFile));

        $this->assertSame('new-image.jpg', $artwork->imageFilename());
    }

    public function test_execute__when_no_image_provided__should_not_call_image_processor(): void
    {
        $artwork = $this->buildArtwork();
        $artwork->pullDomainEvents();

        $this->artworkRepository->method('findById')->willReturn($artwork);
        $this->artworkRepository->method('save');
        $this->dispatcher->method('dispatch');

        $this->imageProcessor->expects($this->never())->method('process');

        $this->service->execute($this->buildCommand(id: $artwork->id()->value(), imageFile: null));

        $this->assertSame('original.jpg', $artwork->imageFilename());
    }

    public function test_execute__when_price_updated_to_null__should_clear_price(): void
    {
        $artwork = $this->buildArtwork(price: Price::create(100.00));
        $artwork->pullDomainEvents();

        $this->artworkRepository->method('findById')->willReturn($artwork);
        $this->artworkRepository->method('save');
        $this->dispatcher->method('dispatch');

        $this->service->execute($this->buildCommand(id: $artwork->id()->value(), price: null));

        $this->assertNull($artwork->price());
    }

    public function test_execute__should_remove_old_categories_and_assign_new_ones(): void
    {
        $oldCategory = $this->buildCategory('Grabado', 'grabado');
        $newCategory = $this->buildCategory('Ilustración', 'ilustracion');

        $artwork = $this->buildArtwork();
        $artwork->assignCategory($oldCategory);
        $artwork->pullDomainEvents();

        $this->artworkRepository->method('findById')->willReturn($artwork);
        $this->artworkRepository->method('save');
        $this->dispatcher->method('dispatch');

        $this->categoryRepository->method('findById')->willReturn($newCategory);

        $this->service->execute($this->buildCommand(
            id:          $artwork->id()->value(),
            categoryIds: [CategoryId::generate()->value()],
        ));

        $this->assertCount(1, $artwork->categories());
        $this->assertSame('ilustracion', $artwork->categories()->first()->slug()->value());
    }

    public function test_execute__when_no_new_categories__should_clear_all(): void
    {
        $category = $this->buildCategory('Grabado', 'grabado');

        $artwork = $this->buildArtwork();
        $artwork->assignCategory($category);
        $artwork->pullDomainEvents();

        $this->artworkRepository->method('findById')->willReturn($artwork);
        $this->artworkRepository->method('save');
        $this->dispatcher->method('dispatch');

        $this->service->execute($this->buildCommand(id: $artwork->id()->value(), categoryIds: []));

        $this->assertCount(0, $artwork->categories());
    }

    private function buildCommand(
        string $id = '',
        string $title = 'Título',
        string $technique = 'Óleo',
        int $year = 2024,
        ?float $price = 100.00,
        ?UploadedFile $imageFile = null,
        array $categoryIds = [],
        array $tagIds = [],
    ): UpdateArtworkCommand {
        return UpdateArtworkCommand::create(
            id:          $id ?: ArtworkId::generate()->value(),
            title:       $title,
            description: null,
            technique:   $technique,
            dimensions:  '50x60',
            year:        $year,
            price:       $price,
            imageFile:   $imageFile,
            shopUrl:     null,
            isAvailable: true,
            categoryIds: $categoryIds,
            tagIds:      $tagIds,
        );
    }

    private function buildArtwork(?Price $price = null): Artwork
    {
        return Artwork::create(
            id:            ArtworkId::generate(),
            title:         ArtworkTitle::create('Título original'),
            description:   null,
            technique:     Technique::create('Óleo'),
            dimensions:    Dimensions::create('50x60'),
            year:          ArtworkYear::create(2023),
            price:         $price,
            imageFilename: 'original.jpg',
            shopUrl:       null,
            isAvailable:   true,
            sortOrder:     1,
        );
    }

    private function buildCategory(string $name = 'Ilustración', string $slug = 'ilustracion'): Category
    {
        return Category::create(
            CategoryId::generate(),
            CategoryName::create($name),
            CategorySlug::create($slug),
            1,
        );
    }
}
