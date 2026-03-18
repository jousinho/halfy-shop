<?php

declare(strict_types=1);

namespace App\Application\Artwork\Reorder;

use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Artwork\ValueObject\ArtworkId;

final class ReorderArtworksService
{
    public function __construct(
        private readonly ArtworkRepository $artworkRepository,
    ) {}

    public function execute(ReorderArtworksCommand $command): void
    {
        $artworks = $this->findAllArtworks($command->orderedIds);
        $this->applySortOrder($artworks, $command->orderedIds);
        $this->saveAll($artworks);
    }

    private function findAllArtworks(array $orderedIds): array
    {
        $artworks = [];

        foreach ($orderedIds as $id) {
            $artwork = $this->artworkRepository->findById(ArtworkId::create($id));
            if ($artwork !== null) {
                $artworks[$id] = $artwork;
            }
        }

        return $artworks;
    }

    private function applySortOrder(array $artworks, array $orderedIds): void
    {
        foreach ($orderedIds as $position => $id) {
            if (isset($artworks[$id])) {
                $artworks[$id]->setSortOrder($position + 1);
            }
        }
    }

    private function saveAll(array $artworks): void
    {
        foreach ($artworks as $artwork) {
            $this->artworkRepository->save($artwork);
        }
    }
}
