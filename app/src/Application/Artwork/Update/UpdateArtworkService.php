<?php

declare(strict_types=1);

namespace App\Application\Artwork\Update;

use App\Application\Shared\ImageProcessor;
use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Artwork\ValueObject\ArtworkId;
use App\Domain\Artwork\ValueObject\ArtworkTitle;
use App\Domain\Artwork\ValueObject\ArtworkYear;
use App\Domain\Artwork\ValueObject\Dimensions;
use App\Domain\Artwork\ValueObject\Price;
use App\Domain\Artwork\ValueObject\Technique;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Tag\Repository\TagRepository;
use App\Domain\Tag\ValueObject\TagId;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class UpdateArtworkService
{
    public function __construct(
        private readonly ArtworkRepository $artworkRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly TagRepository $tagRepository,
        private readonly ImageProcessor $imageProcessor,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public function execute(UpdateArtworkCommand $command): void
    {
        $artwork = $this->findArtworkOrFail($command->id);
        $this->updateArtworkData($artwork, $command);
        $this->updateImageIfProvided($artwork, $command->imageFile);
        $this->syncCategoriesAndTags($artwork, $command->categoryIds, $command->tagIds);
        $this->save($artwork);
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

    private function updateArtworkData(Artwork $artwork, UpdateArtworkCommand $command): void
    {
        $artwork->update(
            title:       ArtworkTitle::create($command->title),
            description: $command->description,
            technique:   Technique::create($command->technique),
            dimensions:  Dimensions::create($command->dimensions),
            year:        ArtworkYear::create($command->year),
            price:       $command->price !== null ? Price::create($command->price) : null,
            shopUrl:     $command->shopUrl,
            isAvailable: $command->isAvailable,
        );
    }

    private function updateImageIfProvided(Artwork $artwork, ?UploadedFile $imageFile): void
    {
        if ($imageFile === null) {
            return;
        }

        $imageFilename = $this->imageProcessor->process($imageFile, 'artworks');
        $artwork->updateImage($imageFilename);
    }

    private function syncCategoriesAndTags(Artwork $artwork, array $categoryIds, array $tagIds): void
    {
        foreach ($artwork->categories()->toArray() as $category) {
            $artwork->removeCategory($category);
        }

        foreach ($artwork->tags()->toArray() as $tag) {
            $artwork->removeTag($tag);
        }

        foreach ($categoryIds as $categoryId) {
            $category = $this->categoryRepository->findById(CategoryId::create($categoryId));
            if ($category !== null) {
                $artwork->assignCategory($category);
            }
        }

        foreach ($tagIds as $tagId) {
            $tag = $this->tagRepository->findById(TagId::create($tagId));
            if ($tag !== null) {
                $artwork->assignTag($tag);
            }
        }
    }

    private function save(Artwork $artwork): void
    {
        $this->artworkRepository->save($artwork);
    }

    private function dispatchEvents(Artwork $artwork): void
    {
        foreach ($artwork->pullDomainEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }
    }
}
