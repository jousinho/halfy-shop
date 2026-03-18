<?php

declare(strict_types=1);

namespace App\Application\Artwork\Create;

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
use App\Domain\Tag\Repository\TagRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class CreateArtworkService
{
    public function __construct(
        private readonly ArtworkRepository $artworkRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly TagRepository $tagRepository,
        private readonly ImageProcessor $imageProcessor,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public function execute(CreateArtworkCommand $command): void
    {
        $imageFilename = $this->processAndStoreImage($command->imageFile);
        $artwork       = $this->buildArtwork($command, $imageFilename);
        $this->assignCategoriesAndTags($artwork, $command->categoryIds, $command->tagIds);
        $this->save($artwork);
        $this->dispatchEvents($artwork);
    }

    private function processAndStoreImage(UploadedFile $file): string
    {
        return $this->imageProcessor->process($file, 'artworks');
    }

    private function buildArtwork(CreateArtworkCommand $command, string $imageFilename): Artwork
    {
        return Artwork::create(
            id:            ArtworkId::generate(),
            title:         ArtworkTitle::create($command->title),
            description:   $command->description,
            technique:     Technique::create($command->technique),
            dimensions:    Dimensions::create($command->dimensions),
            year:          ArtworkYear::create($command->year),
            price:         $command->price !== null ? Price::create($command->price) : null,
            imageFilename: $imageFilename,
            shopUrl:       $command->shopUrl,
            isAvailable:   $command->isAvailable,
            sortOrder:     $this->artworkRepository->findNextSortOrder(),
        );
    }

    private function assignCategoriesAndTags(Artwork $artwork, array $categoryIds, array $tagIds): void
    {
        foreach ($categoryIds as $categoryId) {
            $category = $this->categoryRepository->findById(
                \App\Domain\Category\ValueObject\CategoryId::create($categoryId)
            );
            if ($category !== null) {
                $artwork->assignCategory($category);
            }
        }

        foreach ($tagIds as $tagId) {
            $tag = $this->tagRepository->findById(
                \App\Domain\Tag\ValueObject\TagId::create($tagId)
            );
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
