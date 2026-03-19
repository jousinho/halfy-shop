<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Public;

use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategorySlug;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GalleryController extends AbstractController
{
    public function __construct(
        private readonly ArtworkRepository $artworkRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {}

    #[Route('/categoria/{slug}', name: 'gallery_by_category')]
    public function byCategory(string $slug): Response
    {
        $category = $this->categoryRepository->findBySlug(CategorySlug::create($slug));

        if ($category === null) {
            throw $this->createNotFoundException(sprintf('Category "%s" not found.', $slug));
        }

        return $this->render('public/gallery/by_category.html.twig', [
            'artworks'       => $this->artworkRepository->findByCategory($category->id()),
            'activeCategory' => $category,
            'categories'     => $this->categoryRepository->findAll(),
        ]);
    }
}
