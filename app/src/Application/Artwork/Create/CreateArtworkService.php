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
use App\Domain\Category\ValueObject\CategoryId;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class CreateArtworkService
{
    public function __construct(
        private readonly ArtworkRepository $artworkRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly ImageProcessor $imageProcessor,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public function execute(CreateArtworkCommand $command): void
    {
        $imageFilename = $this->processAndStoreImage($command->imageFile);
        $artwork       = $this->buildArtwork($command, $imageFilename);
        $this->assignCategories($artwork, $command->categoryIds);
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
            titleEn:       $command->titleEn,
            description:   $command->description,
            descriptionEn: $command->descriptionEn,
            technique:     Technique::create($command->technique),
            techniqueEn:   $command->techniqueEn,
            dimensions:    Dimensions::create($command->dimensions),
            year:          ArtworkYear::create($command->year),
            price:         $command->price !== null ? Price::create($command->price) : null,
            imageFilename: $imageFilename,
            shopUrl:       $command->shopUrl,
            isAvailable:   $command->isAvailable,
            sortOrder:     $this->artworkRepository->findNextSortOrder(),
        );
    }

    private function assignCategories(Artwork $artwork, array $categoryIds): void
    {
        foreach ($categoryIds as $categoryId) {
            $category = $this->categoryRepository->findById(CategoryId::create($categoryId));
            if ($category !== null) {
                $artwork->assignCategory($category);
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
