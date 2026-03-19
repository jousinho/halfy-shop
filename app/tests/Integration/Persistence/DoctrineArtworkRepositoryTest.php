<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence;

use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Artwork\ValueObject\ArtworkId;
use App\Domain\Artwork\ValueObject\ArtworkTitle;
use App\Domain\Artwork\ValueObject\ArtworkYear;
use App\Domain\Artwork\ValueObject\Dimensions;
use App\Domain\Artwork\ValueObject\Technique;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Category\ValueObject\CategoryName;
use App\Domain\Category\ValueObject\CategorySlug;
use App\Tests\Integration\IntegrationTestCase;

final class DoctrineArtworkRepositoryTest extends IntegrationTestCase
{
    private ArtworkRepository $artworkRepository;
    private CategoryRepository $categoryRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artworkRepository  = $this->getService(ArtworkRepository::class);
        $this->categoryRepository = $this->getService(CategoryRepository::class);
    }

    public function test_save_and_findById__should_persist_and_retrieve(): void
    {
        $artwork = $this->buildArtwork();
        $this->artworkRepository->save($artwork);

        $found = $this->artworkRepository->findById($artwork->id());

        $this->assertNotNull($found);
        $this->assertSame($artwork->id()->value(), $found->id()->value());
        $this->assertSame('Test Artwork', $found->title()->value());
    }

    public function test_findById__when_not_exists__should_return_null(): void
    {
        $result = $this->artworkRepository->findById(ArtworkId::generate());

        $this->assertNull($result);
    }

    public function test_findAll__should_return_all_saved_artworks(): void
    {
        $this->artworkRepository->save($this->buildArtwork(sortOrder: 1));
        $this->artworkRepository->save($this->buildArtwork(sortOrder: 2));

        $all = $this->artworkRepository->findAll();

        $this->assertGreaterThanOrEqual(2, count($all));
    }

    public function test_delete__should_remove_artwork(): void
    {
        $artwork = $this->buildArtwork();
        $this->artworkRepository->save($artwork);

        $this->artworkRepository->delete($artwork);

        $this->assertNull($this->artworkRepository->findById($artwork->id()));
    }

    public function test_findByShopUrl__should_return_matching_artwork(): void
    {
        $artwork = $this->buildArtwork(shopUrl: 'https://tienda.bigcartel.com/product/test-123');
        $this->artworkRepository->save($artwork);

        $found = $this->artworkRepository->findByShopUrl('https://tienda.bigcartel.com/product/test-123');

        $this->assertNotNull($found);
        $this->assertSame($artwork->id()->value(), $found->id()->value());
    }

    public function test_findByShopUrl__when_not_exists__should_return_null(): void
    {
        $result = $this->artworkRepository->findByShopUrl('https://no-existe.com/product/xyz');

        $this->assertNull($result);
    }

    public function test_findNextSortOrder__should_return_max_plus_one(): void
    {
        $this->artworkRepository->save($this->buildArtwork(sortOrder: 5));
        $this->artworkRepository->save($this->buildArtwork(sortOrder: 8));

        $next = $this->artworkRepository->findNextSortOrder();

        $this->assertGreaterThanOrEqual(9, $next);
    }

    public function test_findByCategory__should_return_artworks_in_category(): void
    {
        $category = Category::create(
            CategoryId::generate(),
            CategoryName::create('Grabado'),
            CategorySlug::create('grabado'),
            1,
        );
        $this->categoryRepository->save($category);

        $artwork = $this->buildArtwork();
        $artwork->assignCategory($category);
        $this->artworkRepository->save($artwork);

        $found = $this->artworkRepository->findByCategory($category->id());

        $this->assertCount(1, $found);
        $this->assertSame($artwork->id()->value(), $found[0]->id()->value());
    }

    private function buildArtwork(int $sortOrder = 1, ?string $shopUrl = null): Artwork
    {
        return Artwork::create(
            id:            ArtworkId::generate(),
            title:         ArtworkTitle::create('Test Artwork'),
            description:   null,
            technique:     Technique::create('Óleo'),
            dimensions:    Dimensions::create('30x40'),
            year:          ArtworkYear::create(2024),
            price:         null,
            imageFilename: 'test.jpg',
            shopUrl:       $shopUrl,
            isAvailable:   true,
            sortOrder:     $sortOrder,
        );
    }
}
