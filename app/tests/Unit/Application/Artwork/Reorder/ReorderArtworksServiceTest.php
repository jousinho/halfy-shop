<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Artwork\Reorder;

use App\Application\Artwork\Reorder\ReorderArtworksCommand;
use App\Application\Artwork\Reorder\ReorderArtworksService;
use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Artwork\ValueObject\ArtworkId;
use App\Domain\Artwork\ValueObject\ArtworkTitle;
use App\Domain\Artwork\ValueObject\ArtworkYear;
use App\Domain\Artwork\ValueObject\Dimensions;
use App\Domain\Artwork\ValueObject\Technique;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class ReorderArtworksServiceTest extends TestCase
{
    private ArtworkRepository&MockObject $artworkRepository;
    private ReorderArtworksService $service;

    protected function setUp(): void
    {
        $this->artworkRepository = $this->createMock(ArtworkRepository::class);
        $this->service           = new ReorderArtworksService($this->artworkRepository);
    }

    public function test_execute__should_apply_sort_order_in_given_position(): void
    {
        $artwork1 = $this->buildArtwork(sortOrder: 3);
        $artwork2 = $this->buildArtwork(sortOrder: 1);
        $artwork3 = $this->buildArtwork(sortOrder: 2);

        $this->configureRepositoryWith($artwork1, $artwork2, $artwork3);

        $command = ReorderArtworksCommand::create([
            $artwork1->id()->value(),
            $artwork2->id()->value(),
            $artwork3->id()->value(),
        ]);

        $this->service->execute($command);

        $this->assertSame(1, $artwork1->sortOrder());
        $this->assertSame(2, $artwork2->sortOrder());
        $this->assertSame(3, $artwork3->sortOrder());
    }

    public function test_execute__sort_order_starts_at_one_not_zero(): void
    {
        $artwork = $this->buildArtwork(sortOrder: 5);

        $this->configureRepositoryWith($artwork);
        $this->artworkRepository->method('save');

        $this->service->execute(ReorderArtworksCommand::create([$artwork->id()->value()]));

        $this->assertSame(1, $artwork->sortOrder());
    }

    public function test_execute__should_save_each_reordered_artwork(): void
    {
        $artwork1 = $this->buildArtwork();
        $artwork2 = $this->buildArtwork();

        $this->configureRepositoryWith($artwork1, $artwork2);

        $this->artworkRepository
            ->expects($this->exactly(2))
            ->method('save');

        $this->service->execute(ReorderArtworksCommand::create([
            $artwork1->id()->value(),
            $artwork2->id()->value(),
        ]));
    }

    public function test_execute__when_unknown_id_in_list__should_skip_it(): void
    {
        $artwork = $this->buildArtwork(sortOrder: 5);

        $this->configureRepositoryWith($artwork);

        $this->artworkRepository->expects($this->once())->method('save');

        $command = ReorderArtworksCommand::create([
            $artwork->id()->value(),
            ArtworkId::generate()->value(),
        ]);

        $this->service->execute($command);

        $this->assertSame(1, $artwork->sortOrder());
    }

    public function test_execute__when_list_is_empty__should_not_call_save(): void
    {
        $this->artworkRepository->expects($this->never())->method('save');

        $this->service->execute(ReorderArtworksCommand::create([]));
    }

    private function configureRepositoryWith(Artwork ...$artworks): void
    {
        $map = [];
        foreach ($artworks as $artwork) {
            $map[$artwork->id()->value()] = $artwork;
        }

        $this->artworkRepository
            ->method('findById')
            ->willReturnCallback(fn(ArtworkId $id) => $map[$id->value()] ?? null);
    }

    private function buildArtwork(int $sortOrder = 1): Artwork
    {
        return Artwork::create(
            id:            ArtworkId::generate(),
            title:         ArtworkTitle::create('Obra'),
            description:   null,
            technique:     Technique::create('Óleo'),
            dimensions:    Dimensions::create('50x60'),
            year:          ArtworkYear::create(2023),
            price:         null,
            imageFilename: 'img.jpg',
            shopUrl:       null,
            isAvailable:   true,
            sortOrder:     $sortOrder,
        );
    }
}
