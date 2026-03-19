<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Public;

use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Category\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly ArtworkRepository $artworkRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {}

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('public/home/index.html.twig', [
            'artworks'   => $this->artworkRepository->findAll(),
            'categories' => $this->categoryRepository->findAll(),
        ]);
    }
}
