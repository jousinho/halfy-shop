<?php

declare(strict_types=1);

namespace App\Application\About;

use App\Application\Shared\ImageProcessor;
use App\Domain\About\Entity\AboutPage;
use App\Domain\About\Repository\AboutPageRepository;
use App\Domain\About\ValueObject\AboutPageId;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UpdateAboutService
{
    public function __construct(
        private readonly AboutPageRepository $aboutPageRepository,
        private readonly ImageProcessor $imageProcessor,
    ) {}

    public function execute(UpdateAboutCommand $command): void
    {
        $page          = $this->findOrCreateAboutPage();
        $photoFilename = $this->resolvePhotoFilename($page, $command->photoFile, $command->removePhoto);
        $page->update($command->content, $photoFilename);
        $this->save($page);
    }

    private function findOrCreateAboutPage(): AboutPage
    {
        return $this->aboutPageRepository->findPage()
            ?? AboutPage::create(AboutPageId::generate(), '', null);
    }

    private function resolvePhotoFilename(
        AboutPage $page,
        ?UploadedFile $photoFile,
        bool $removePhoto,
    ): ?string {
        if ($photoFile !== null) {
            return $this->imageProcessor->process($photoFile, 'about');
        }

        if ($removePhoto) {
            return null;
        }

        return $page->photoFilename();
    }

    private function save(AboutPage $page): void
    {
        $this->aboutPageRepository->save($page);
    }
}
