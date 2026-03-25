<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application;

use App\Application\Shared\BigCartelFeedFetcher;
use App\Application\Sync\SyncWithBigCartelService;
use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Artwork\ValueObject\ArtworkId;
use App\Domain\Artwork\ValueObject\ArtworkTitle;
use App\Domain\Artwork\ValueObject\ArtworkYear;
use App\Domain\Artwork\ValueObject\Dimensions;
use App\Domain\Artwork\ValueObject\Technique;
use App\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
final class SyncWithBigCartelServiceIntegrationTest extends IntegrationTestCase
{
    private ArtworkRepository $artworkRepository;
    private BigCartelFeedFetcher $feedFetcher;
    private \App\Application\Shared\RemoteImageDownloader $imageDownloader;
    private SyncWithBigCartelService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artworkRepository = $this->getService(ArtworkRepository::class);
        $this->feedFetcher       = $this->createMock(BigCartelFeedFetcher::class);
        $this->imageDownloader   = $this->createMock(\App\Application\Shared\RemoteImageDownloader::class);
        $this->imageDownloader->method('download')->willReturn('synced.jpg');

        $this->service = new SyncWithBigCartelService(
            feedFetcher:      $this->feedFetcher,
            artworkRepository: $this->artworkRepository,
            syncLogRepository: $this->getService(\App\Domain\Sync\Repository\SyncLogRepository::class),
            imageDownloader:  $this->imageDownloader,
            feedUrl:          'https://fake.bigcartel.com/products.xml',
        );
    }

    public function test_execute__when_artwork_is_new__should_create_it(): void
    {
        $this->feedFetcher->method('fetch')->willReturn([[
            'title'       => 'Nueva Obra',
            'description' => 'Descripción',
            'technique'   => 'Acuarela',
            'dimensions'  => '30 x 40 cm',
            'price'       => 150.00,
            'shopUrl'     => 'https://tienda.bigcartel.com/product/nueva-obra',
            'imageUrl'    => 'https://fake.bigcartel.com/img/nueva-obra.jpg',
            'isAvailable' => true,
        ]]);

        $log = $this->service->execute();

        $this->assertSame(1, $log->created());
        $this->assertSame(0, $log->updated());
        $this->assertSame(0, $log->unchanged());

        $saved = $this->artworkRepository->findByShopUrl('https://tienda.bigcartel.com/product/nueva-obra');
        $this->assertNotNull($saved);
        $this->assertSame('Nueva Obra', $saved->title()->value());
    }

    public function test_execute__when_artwork_exists_and_unchanged__should_count_as_unchanged(): void
    {
        $existing = Artwork::create(
            id:            ArtworkId::generate(),
            title:         ArtworkTitle::create('Obra Existente'),
            description:   null,
            technique:     Technique::create('Óleo'),
            dimensions:    Dimensions::create('30x40'),
            year:          ArtworkYear::create(2024),
            price:         null,
            imageFilename: 'existing.jpg',
            shopUrl:       'https://tienda.bigcartel.com/product/existente',
            isAvailable:   true,
            sortOrder:     1,
        );
        $this->artworkRepository->save($existing);

        $this->feedFetcher->method('fetch')->willReturn([[
            'title'       => 'Obra Existente',
            'description' => 'Desc',
            'technique'   => 'Óleo',
            'dimensions'  => '30x40',
            'price'       => null,
            'shopUrl'     => 'https://tienda.bigcartel.com/product/existente',
            'imageUrl'    => null,
            'isAvailable' => true,
        ]]);

        $log = $this->service->execute();

        $this->assertSame(0, $log->created());
        $this->assertSame(0, $log->updated());
        $this->assertSame(1, $log->unchanged());
    }

    public function test_execute__when_artwork_title_changed__should_update_it(): void
    {
        $existing = Artwork::create(
            id:            ArtworkId::generate(),
            title:         ArtworkTitle::create('Título Antiguo'),
            description:   null,
            technique:     Technique::create('Óleo'),
            dimensions:    Dimensions::create('30x40'),
            year:          ArtworkYear::create(2024),
            price:         null,
            imageFilename: 'old.jpg',
            shopUrl:       'https://tienda.bigcartel.com/product/cambio',
            isAvailable:   true,
            sortOrder:     1,
        );
        $this->artworkRepository->save($existing);

        $this->feedFetcher->method('fetch')->willReturn([[
            'title'       => 'Título Nuevo',
            'description' => 'Desc',
            'technique'   => 'Óleo',
            'dimensions'  => '30x40',
            'price'       => null,
            'shopUrl'     => 'https://tienda.bigcartel.com/product/cambio',
            'imageUrl'    => null,
            'isAvailable' => true,
        ]]);

        $log = $this->service->execute();

        $this->assertSame(0, $log->created());
        $this->assertSame(1, $log->updated());
        $this->assertSame(0, $log->unchanged());
    }

    public function test_execute__when_feed_is_empty__should_return_zero_counts(): void
    {
        $this->feedFetcher->method('fetch')->willReturn([]);

        $log = $this->service->execute();

        $this->assertSame(0, $log->created());
        $this->assertSame(0, $log->updated());
        $this->assertSame(0, $log->unchanged());
    }

    public function test_execute__should_persist_sync_log(): void
    {
        $this->feedFetcher->method('fetch')->willReturn([]);

        $this->service->execute();

        $syncLogRepo = $this->getService(\App\Domain\Sync\Repository\SyncLogRepository::class);
        $latest      = $syncLogRepo->findLatest();

        $this->assertNotNull($latest);
    }
}
