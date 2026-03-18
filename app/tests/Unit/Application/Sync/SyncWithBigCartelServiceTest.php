<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Sync;

use App\Application\Shared\BigCartelFeedFetcher;
use App\Application\Shared\RemoteImageDownloader;
use App\Application\Sync\SyncWithBigCartelService;
use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Artwork\ValueObject\ArtworkId;
use App\Domain\Artwork\ValueObject\ArtworkTitle;
use App\Domain\Artwork\ValueObject\ArtworkYear;
use App\Domain\Artwork\ValueObject\Dimensions;
use App\Domain\Artwork\ValueObject\Price;
use App\Domain\Artwork\ValueObject\Technique;
use App\Domain\Sync\Entity\SyncLog;
use App\Domain\Sync\Repository\SyncLogRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SyncWithBigCartelServiceTest extends TestCase
{
    private BigCartelFeedFetcher&MockObject $feedFetcher;
    private ArtworkRepository&MockObject $artworkRepository;
    private SyncLogRepository&MockObject $syncLogRepository;
    private RemoteImageDownloader&MockObject $imageDownloader;
    private SyncWithBigCartelService $service;

    protected function setUp(): void
    {
        $this->feedFetcher       = $this->createMock(BigCartelFeedFetcher::class);
        $this->artworkRepository = $this->createMock(ArtworkRepository::class);
        $this->syncLogRepository = $this->createMock(SyncLogRepository::class);
        $this->imageDownloader   = $this->createMock(RemoteImageDownloader::class);

        $this->service = new SyncWithBigCartelService(
            $this->feedFetcher,
            $this->artworkRepository,
            $this->syncLogRepository,
            $this->imageDownloader,
            'https://example.bigcartel.com/products.xml',
        );
    }

    public function test_execute__when_feed_is_empty__should_return_log_with_zeros(): void
    {
        $this->feedFetcher->method('fetch')->willReturn([]);

        $log = $this->service->execute();

        $this->assertSame(0, $log->created());
        $this->assertSame(0, $log->updated());
        $this->assertSame(0, $log->unchanged());
    }

    public function test_execute__when_artwork_not_exists__should_create_it(): void
    {
        $this->feedFetcher->method('fetch')->willReturn([$this->buildFeedItem()]);
        $this->artworkRepository->method('findByShopUrl')->willReturn(null);
        $this->artworkRepository->method('findNextSortOrder')->willReturn(1);
        $this->imageDownloader->method('download')->willReturn('synced.jpg');

        $this->artworkRepository->expects($this->once())->method('save');

        $log = $this->service->execute();

        $this->assertSame(1, $log->created());
        $this->assertSame(0, $log->updated());
        $this->assertSame(0, $log->unchanged());
    }

    public function test_execute__when_artwork_exists_and_unchanged__should_not_save(): void
    {
        $item    = $this->buildFeedItem(title: 'Fluye', price: 100.0, isAvailable: true);
        $artwork = $this->buildArtwork(title: 'Fluye', price: 100.0, isAvailable: true);

        $this->feedFetcher->method('fetch')->willReturn([$item]);
        $this->artworkRepository->method('findByShopUrl')->willReturn($artwork);

        $this->artworkRepository->expects($this->never())->method('save');

        $log = $this->service->execute();

        $this->assertSame(0, $log->created());
        $this->assertSame(0, $log->updated());
        $this->assertSame(1, $log->unchanged());
    }

    public function test_execute__when_artwork_exists_and_title_changed__should_update(): void
    {
        $item    = $this->buildFeedItem(title: 'Título nuevo');
        $artwork = $this->buildArtwork(title: 'Título viejo');

        $this->feedFetcher->method('fetch')->willReturn([$item]);
        $this->artworkRepository->method('findByShopUrl')->willReturn($artwork);

        $this->artworkRepository->expects($this->once())->method('save');

        $log = $this->service->execute();

        $this->assertSame(0, $log->created());
        $this->assertSame(1, $log->updated());
        $this->assertSame(0, $log->unchanged());
    }

    public function test_execute__when_artwork_availability_changed__should_update(): void
    {
        $item    = $this->buildFeedItem(isAvailable: false);
        $artwork = $this->buildArtwork(isAvailable: true);

        $this->feedFetcher->method('fetch')->willReturn([$item]);
        $this->artworkRepository->method('findByShopUrl')->willReturn($artwork);
        $this->artworkRepository->method('save');

        $log = $this->service->execute();

        $this->assertSame(1, $log->updated());
    }

    public function test_execute__when_artwork_price_changed__should_update(): void
    {
        $item    = $this->buildFeedItem(price: 200.0);
        $artwork = $this->buildArtwork(price: 100.0);

        $this->feedFetcher->method('fetch')->willReturn([$item]);
        $this->artworkRepository->method('findByShopUrl')->willReturn($artwork);
        $this->artworkRepository->method('save');

        $log = $this->service->execute();

        $this->assertSame(1, $log->updated());
    }

    public function test_execute__with_multiple_items__should_count_correctly(): void
    {
        $newItem       = $this->buildFeedItem(shopUrl: 'https://shop.com/new', title: 'Nueva');
        $changedItem   = $this->buildFeedItem(shopUrl: 'https://shop.com/changed', title: 'Cambiada');
        $unchangedItem = $this->buildFeedItem(shopUrl: 'https://shop.com/same', title: 'Igual');

        $existingChanged   = $this->buildArtwork(shopUrl: 'https://shop.com/changed', title: 'Original');
        $existingUnchanged = $this->buildArtwork(shopUrl: 'https://shop.com/same', title: 'Igual');

        $this->feedFetcher->method('fetch')->willReturn([$newItem, $changedItem, $unchangedItem]);
        $this->artworkRepository
            ->method('findByShopUrl')
            ->willReturnCallback(fn(string $url) => match ($url) {
                'https://shop.com/new'       => null,
                'https://shop.com/changed'   => $existingChanged,
                'https://shop.com/same'      => $existingUnchanged,
                default                      => null,
            });
        $this->artworkRepository->method('findNextSortOrder')->willReturn(1);
        $this->imageDownloader->method('download')->willReturn('img.jpg');
        $this->artworkRepository->method('save');

        $log = $this->service->execute();

        $this->assertSame(1, $log->created());
        $this->assertSame(1, $log->updated());
        $this->assertSame(1, $log->unchanged());
        $this->assertSame(3, $log->total());
    }

    public function test_execute__should_save_sync_log(): void
    {
        $this->feedFetcher->method('fetch')->willReturn([]);

        $this->syncLogRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(SyncLog::class));

        $this->service->execute();
    }

    public function test_execute__should_return_sync_log(): void
    {
        $this->feedFetcher->method('fetch')->willReturn([]);

        $log = $this->service->execute();

        $this->assertInstanceOf(SyncLog::class, $log);
    }

    public function test_execute__log_text__should_contain_item_titles(): void
    {
        $this->feedFetcher->method('fetch')->willReturn([
            $this->buildFeedItem(title: 'Obra uno', shopUrl: 'https://shop.com/1'),
            $this->buildFeedItem(title: 'Obra dos', shopUrl: 'https://shop.com/2'),
        ]);
        $this->artworkRepository->method('findByShopUrl')->willReturn(null);
        $this->artworkRepository->method('findNextSortOrder')->willReturn(1);
        $this->imageDownloader->method('download')->willReturn('img.jpg');
        $this->artworkRepository->method('save');

        $log = $this->service->execute();

        $this->assertStringContainsString('Obra uno', $log->log());
        $this->assertStringContainsString('Obra dos', $log->log());
    }

    public function test_execute__should_fetch_feed_using_configured_url(): void
    {
        $this->feedFetcher
            ->expects($this->once())
            ->method('fetch')
            ->with('https://example.bigcartel.com/products.xml')
            ->willReturn([]);

        $this->service->execute();
    }

    private function buildFeedItem(
        string $title = 'Fluye',
        string $shopUrl = 'https://annapownall.bigcartel.com/product/fluye',
        ?float $price = 100.0,
        bool $isAvailable = true,
    ): array {
        return [
            'title'       => $title,
            'description' => 'Descripción de la obra',
            'price'       => $price,
            'shopUrl'     => $shopUrl,
            'imageUrl'    => 'https://images.bigcartel.com/fluye.jpg',
            'isAvailable' => $isAvailable,
        ];
    }

    private function buildArtwork(
        string $title = 'Fluye',
        string $shopUrl = 'https://annapownall.bigcartel.com/product/fluye',
        ?float $price = 100.0,
        bool $isAvailable = true,
    ): Artwork {
        return Artwork::create(
            id:            ArtworkId::generate(),
            title:         ArtworkTitle::create($title),
            description:   null,
            technique:     Technique::create('—'),
            dimensions:    Dimensions::create('—'),
            year:          ArtworkYear::create((int) date('Y')),
            price:         $price !== null ? Price::create($price) : null,
            imageFilename: 'img.jpg',
            shopUrl:       $shopUrl,
            isAvailable:   $isAvailable,
            sortOrder:     1,
        );
    }
}
