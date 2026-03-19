<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Artwork\Create;

use App\Application\Artwork\Create\CreateArtworkCommand;
use App\Application\Artwork\Create\CreateArtworkService;
use App\Application\Shared\ImageProcessor;
use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Event\ArtworkCreated;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Category\ValueObject\CategoryName;
use App\Domain\Category\ValueObject\CategorySlug;
use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\Repository\TagRepository;
use App\Domain\Tag\ValueObject\TagId;
use App\Domain\Tag\ValueObject\TagName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AllowMockObjectsWithoutExpectations]
final class CreateArtworkServiceTest extends TestCase
{
    private ArtworkRepository&MockObject $artworkRepository;
    private CategoryRepository&MockObject $categoryRepository;
    private TagRepository&MockObject $tagRepository;
    private ImageProcessor&MockObject $imageProcessor;
    private EventDispatcherInterface&MockObject $dispatcher;
    private CreateArtworkService $service;

    protected function setUp(): void
    {
        $this->artworkRepository  = $this->createMock(ArtworkRepository::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->tagRepository      = $this->createMock(TagRepository::class);
        $this->imageProcessor     = $this->createMock(ImageProcessor::class);
        $this->dispatcher         = $this->createMock(EventDispatcherInterface::class);

        $this->service = new CreateArtworkService(
            $this->artworkRepository,
            $this->categoryRepository,
            $this->tagRepository,
            $this->imageProcessor,
            $this->dispatcher,
        );
    }

    public function test_execute__should_save_artwork_and_dispatch_created_event(): void
    {
        $this->imageProcessor->method('process')->willReturn('img.jpg');
        $this->artworkRepository->method('findNextSortOrder')->willReturn(1);

        $this->artworkRepository->expects($this->once())->method('save');
        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ArtworkCreated::class));

        $this->service->execute($this->buildCommand());
    }

    public function test_execute__should_process_image_with_artworks_destination(): void
    {
        $imageFile = $this->createMock(UploadedFile::class);

        $this->imageProcessor
            ->expects($this->once())
            ->method('process')
            ->with($imageFile, 'artworks')
            ->willReturn('img.jpg');

        $this->artworkRepository->method('findNextSortOrder')->willReturn(1);
        $this->artworkRepository->method('save');
        $this->dispatcher->method('dispatch');

        $this->service->execute($this->buildCommand(imageFile: $imageFile));
    }

    public function test_execute__should_use_filename_returned_by_image_processor(): void
    {
        $this->imageProcessor->method('process')->willReturn('processed-result.jpg');
        $this->artworkRepository->method('findNextSortOrder')->willReturn(1);
        $this->dispatcher->method('dispatch');

        $capturedArtwork = null;
        $this->artworkRepository
            ->method('save')
            ->willReturnCallback(function (Artwork $artwork) use (&$capturedArtwork): void {
                $capturedArtwork = $artwork;
            });

        $this->service->execute($this->buildCommand());

        $this->assertSame('processed-result.jpg', $capturedArtwork->imageFilename());
    }

    public function test_execute__should_use_next_sort_order_from_repository(): void
    {
        $this->imageProcessor->method('process')->willReturn('img.jpg');
        $this->artworkRepository->method('findNextSortOrder')->willReturn(7);
        $this->dispatcher->method('dispatch');

        $capturedArtwork = null;
        $this->artworkRepository
            ->method('save')
            ->willReturnCallback(function (Artwork $artwork) use (&$capturedArtwork): void {
                $capturedArtwork = $artwork;
            });

        $this->service->execute($this->buildCommand());

        $this->assertSame(7, $capturedArtwork->sortOrder());
    }

    public function test_execute__when_price_is_null__should_create_artwork_without_price(): void
    {
        $this->imageProcessor->method('process')->willReturn('img.jpg');
        $this->artworkRepository->method('findNextSortOrder')->willReturn(1);
        $this->dispatcher->method('dispatch');

        $capturedArtwork = null;
        $this->artworkRepository
            ->method('save')
            ->willReturnCallback(function (Artwork $artwork) use (&$capturedArtwork): void {
                $capturedArtwork = $artwork;
            });

        $this->service->execute($this->buildCommand(price: null));

        $this->assertNull($capturedArtwork->price());
    }

    public function test_execute__when_category_given__should_assign_category_to_artwork(): void
    {
        $category = $this->buildCategory();

        $this->imageProcessor->method('process')->willReturn('img.jpg');
        $this->artworkRepository->method('findNextSortOrder')->willReturn(1);
        $this->categoryRepository->method('findById')->willReturn($category);
        $this->dispatcher->method('dispatch');

        $capturedArtwork = null;
        $this->artworkRepository
            ->method('save')
            ->willReturnCallback(function (Artwork $artwork) use (&$capturedArtwork): void {
                $capturedArtwork = $artwork;
            });

        $this->service->execute($this->buildCommand(categoryIds: [CategoryId::generate()->value()]));

        $this->assertCount(1, $capturedArtwork->categories());
    }

    public function test_execute__when_tag_given__should_assign_tag_to_artwork(): void
    {
        $tag = $this->buildTag();

        $this->imageProcessor->method('process')->willReturn('img.jpg');
        $this->artworkRepository->method('findNextSortOrder')->willReturn(1);
        $this->tagRepository->method('findById')->willReturn($tag);
        $this->dispatcher->method('dispatch');

        $capturedArtwork = null;
        $this->artworkRepository
            ->method('save')
            ->willReturnCallback(function (Artwork $artwork) use (&$capturedArtwork): void {
                $capturedArtwork = $artwork;
            });

        $this->service->execute($this->buildCommand(tagIds: [TagId::generate()->value()]));

        $this->assertCount(1, $capturedArtwork->tags());
    }

    public function test_execute__when_multiple_categories_given__should_assign_all(): void
    {
        $this->imageProcessor->method('process')->willReturn('img.jpg');
        $this->artworkRepository->method('findNextSortOrder')->willReturn(1);
        $this->dispatcher->method('dispatch');

        $this->categoryRepository
            ->method('findById')
            ->willReturnOnConsecutiveCalls($this->buildCategory(), $this->buildCategory());

        $capturedArtwork = null;
        $this->artworkRepository
            ->method('save')
            ->willReturnCallback(function (Artwork $artwork) use (&$capturedArtwork): void {
                $capturedArtwork = $artwork;
            });

        $this->service->execute($this->buildCommand(
            categoryIds: [CategoryId::generate()->value(), CategoryId::generate()->value()],
        ));

        $this->assertCount(2, $capturedArtwork->categories());
    }

    public function test_execute__when_category_not_found__should_skip_it(): void
    {
        $this->imageProcessor->method('process')->willReturn('img.jpg');
        $this->artworkRepository->method('findNextSortOrder')->willReturn(1);
        $this->categoryRepository->method('findById')->willReturn(null);
        $this->dispatcher->method('dispatch');

        $capturedArtwork = null;
        $this->artworkRepository
            ->method('save')
            ->willReturnCallback(function (Artwork $artwork) use (&$capturedArtwork): void {
                $capturedArtwork = $artwork;
            });

        $this->service->execute($this->buildCommand(categoryIds: [CategoryId::generate()->value()]));

        $this->assertCount(0, $capturedArtwork->categories());
    }

    public function test_execute__when_tag_not_found__should_skip_it(): void
    {
        $this->imageProcessor->method('process')->willReturn('img.jpg');
        $this->artworkRepository->method('findNextSortOrder')->willReturn(1);
        $this->tagRepository->method('findById')->willReturn(null);
        $this->dispatcher->method('dispatch');

        $capturedArtwork = null;
        $this->artworkRepository
            ->method('save')
            ->willReturnCallback(function (Artwork $artwork) use (&$capturedArtwork): void {
                $capturedArtwork = $artwork;
            });

        $this->service->execute($this->buildCommand(tagIds: [TagId::generate()->value()]));

        $this->assertCount(0, $capturedArtwork->tags());
    }

    private function buildCommand(
        ?UploadedFile $imageFile = null,
        ?float $price = 100.00,
        array $categoryIds = [],
        array $tagIds = [],
    ): CreateArtworkCommand {
        return CreateArtworkCommand::create(
            title:       'Fluye',
            description: null,
            technique:   'Óleo',
            dimensions:  '50x60',
            year:        2024,
            price:       $price,
            imageFile:   $imageFile ?? $this->createMock(UploadedFile::class),
            shopUrl:     null,
            isAvailable: true,
            categoryIds: $categoryIds,
            tagIds:      $tagIds,
        );
    }

    private function buildCategory(): Category
    {
        return Category::create(
            CategoryId::generate(),
            CategoryName::create('Ilustración'),
            CategorySlug::create('ilustracion'),
            1,
        );
    }

    private function buildTag(): Tag
    {
        return Tag::create(TagId::generate(), TagName::create('abstracto'), 'abstracto');
    }
}
