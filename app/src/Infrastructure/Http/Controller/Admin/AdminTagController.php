<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Admin;

use App\Application\Tag\Create\CreateTagCommand;
use App\Application\Tag\Create\CreateTagService;
use App\Application\Tag\Delete\DeleteTagCommand;
use App\Application\Tag\Delete\DeleteTagService;
use App\Application\Tag\Update\UpdateTagCommand;
use App\Application\Tag\Update\UpdateTagService;
use App\Domain\Tag\Repository\TagRepository;
use App\Domain\Tag\ValueObject\TagId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/tags')]
final class AdminTagController extends AbstractController
{
    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly CreateTagService $createTagService,
        private readonly UpdateTagService $updateTagService,
        private readonly DeleteTagService $deleteTagService,
    ) {}

    #[Route('', name: 'admin_tags', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/tag/index.html.twig', [
            'tags' => $this->tagRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_tags_new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('admin/tag/form.html.twig', ['tag' => null]);
    }

    #[Route('', name: 'admin_tags_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $this->createTagService->execute(CreateTagCommand::create(
            name: $request->request->getString('name'),
            slug: $request->request->getString('slug'),
        ));

        $this->addFlash('success', 'Tag creado correctamente.');

        return $this->redirectToRoute('admin_tags');
    }

    #[Route('/{id}/edit', name: 'admin_tags_edit', methods: ['GET'])]
    public function edit(string $id): Response
    {
        $tag = $this->tagRepository->findById(TagId::create($id));

        if ($tag === null) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/tag/form.html.twig', ['tag' => $tag]);
    }

    #[Route('/{id}', name: 'admin_tags_update', methods: ['POST'])]
    public function update(string $id, Request $request): Response
    {
        $this->updateTagService->execute(UpdateTagCommand::create(
            id:   $id,
            name: $request->request->getString('name'),
            slug: $request->request->getString('slug'),
        ));

        $this->addFlash('success', 'Tag actualizado correctamente.');

        return $this->redirectToRoute('admin_tags');
    }

    #[Route('/{id}/delete', name: 'admin_tags_delete', methods: ['POST'])]
    public function delete(string $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_tag_' . $id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $this->deleteTagService->execute(DeleteTagCommand::create($id));

        $this->addFlash('success', 'Tag eliminado correctamente.');

        return $this->redirectToRoute('admin_tags');
    }
}
