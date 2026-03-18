<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Public;

use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Blog\ValueObject\PostSlug;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BlogController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
    ) {}

    #[Route('/blog/', name: 'blog_index')]
    public function index(): Response
    {
        return $this->render('public/blog/index.html.twig', [
            'posts' => $this->postRepository->findPublished(),
        ]);
    }

    #[Route('/blog/{slug}/', name: 'blog_show')]
    public function show(string $slug): Response
    {
        $post = $this->postRepository->findBySlug(PostSlug::create($slug));

        if ($post === null || !$post->isPublished()) {
            throw $this->createNotFoundException(sprintf('Post "%s" not found.', $slug));
        }

        return $this->render('public/blog/show.html.twig', [
            'post' => $post,
        ]);
    }
}
