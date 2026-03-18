<?php

declare(strict_types=1);

namespace App\Application\Artwork\Delete;

use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Artwork\ValueObject\ArtworkId;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class DeleteArtworkService
{
    public function __construct(
        private readonly ArtworkRepository $artworkRepository,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly string $uploadsDir,
    ) {}

    public function execute(DeleteArtworkCommand $command): void
    {
        $artwork = $this->findArtworkOrFail($command->id);
        $this->deleteImage($artwork->imageFilename());
        $this->delete($artwork);
        $this->dispatchEvents($artwork);
    }

    private function findArtworkOrFail(string $id): Artwork
    {
        $artwork = $this->artworkRepository->findById(ArtworkId::create($id));

        if ($artwork === null) {
            throw new \RuntimeException(sprintf('Artwork "%s" not found.', $id));
        }

        return $artwork;
    }

    private function deleteImage(string $imageFilename): void
    {
        $path = $this->uploadsDir . '/artworks/' . $imageFilename;

        if (file_exists($path)) {
            unlink($path);
        }

        $thumbnailPath = $this->uploadsDir . '/artworks/thumbnails/' . $imageFilename;

        if (file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }
    }

    private function delete(Artwork $artwork): void
    {
        $artwork->markAsDeleted();
        $this->artworkRepository->delete($artwork);
    }

    private function dispatchEvents(Artwork $artwork): void
    {
        foreach ($artwork->pullDomainEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }
    }
}
