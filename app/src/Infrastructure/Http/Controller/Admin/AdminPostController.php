<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Admin;

use App\Application\Blog\Create\CreatePostCommand;
use App\Application\Blog\Create\CreatePostService;
use App\Application\Blog\Delete\DeletePostCommand;
use App\Application\Blog\Delete\DeletePostService;
use App\Application\Blog\Update\UpdatePostCommand;
use App\Application\Blog\Update\UpdatePostService;
use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Blog\ValueObject\PostId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/posts')]
final class AdminPostController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly CreatePostService $createPostService,
        private readonly UpdatePostService $updatePostService,
        private readonly DeletePostService $deletePostService,
    ) {}

    #[Route('', name: 'admin_posts', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/post/index.html.twig', [
            'posts' => $this->postRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_posts_new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('admin/post/form.html.twig', ['post' => null]);
    }

    #[Route('', name: 'admin_posts_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $this->createPostService->execute(CreatePostCommand::create(
            title:       $request->request->getString('title'),
            slug:        $request->request->getString('slug'),
            content:     $request->request->getString('content'),
            publishedAt: $this->parsePublishedAt($request->request->getString('publishedAt')),
        ));

        $this->addFlash('success', 'Post creado correctamente.');

        return $this->redirectToRoute('admin_posts');
    }

    #[Route('/{id}/edit', name: 'admin_posts_edit', methods: ['GET'])]
    public function edit(string $id): Response
    {
        $post = $this->postRepository->findById(PostId::create($id));

        if ($post === null) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/post/form.html.twig', ['post' => $post]);
    }

    #[Route('/{id}', name: 'admin_posts_update', methods: ['POST'])]
    public function update(string $id, Request $request): Response
    {
        $this->updatePostService->execute(UpdatePostCommand::create(
            id:          $id,
            title:       $request->request->getString('title'),
            slug:        $request->request->getString('slug'),
            content:     $request->request->getString('content'),
            publishedAt: $this->parsePublishedAt($request->request->getString('publishedAt')),
        ));

        $this->addFlash('success', 'Post actualizado correctamente.');

        return $this->redirectToRoute('admin_posts');
    }

    #[Route('/{id}/delete', name: 'admin_posts_delete', methods: ['POST'])]
    public function delete(string $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_post_' . $id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $this->deletePostService->execute(DeletePostCommand::create($id));

        $this->addFlash('success', 'Post eliminado correctamente.');

        return $this->redirectToRoute('admin_posts');
    }

    private function parsePublishedAt(string $value): ?\DateTimeImmutable
    {
        $value = trim($value);

        return $value !== '' ? new \DateTimeImmutable($value) : null;
    }
}
