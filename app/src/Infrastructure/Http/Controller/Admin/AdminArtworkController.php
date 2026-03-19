<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Admin;

use App\Application\Artwork\Create\CreateArtworkCommand;
use App\Application\Artwork\Create\CreateArtworkService;
use App\Application\Artwork\Delete\DeleteArtworkCommand;
use App\Application\Artwork\Delete\DeleteArtworkService;
use App\Application\Artwork\Reorder\ReorderArtworksCommand;
use App\Application\Artwork\Reorder\ReorderArtworksService;
use App\Application\Artwork\Update\UpdateArtworkCommand;
use App\Application\Artwork\Update\UpdateArtworkService;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Artwork\ValueObject\ArtworkId;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Tag\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/artworks')]
final class AdminArtworkController extends AbstractController
{
    public function __construct(
        private readonly ArtworkRepository $artworkRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly TagRepository $tagRepository,
        private readonly CreateArtworkService $createArtworkService,
        private readonly UpdateArtworkService $updateArtworkService,
        private readonly DeleteArtworkService $deleteArtworkService,
        private readonly ReorderArtworksService $reorderArtworksService,
    ) {}

    #[Route('', name: 'admin_artworks', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/artwork/index.html.twig', [
            'artworks' => $this->artworkRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_artworks_new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('admin/artwork/form.html.twig', [
            'artwork'    => null,
            'categories' => $this->categoryRepository->findAll(),
            'tags'       => $this->tagRepository->findAll(),
        ]);
    }

    #[Route('', name: 'admin_artworks_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $imageFile = $request->files->get('imageFile');

        if (!$imageFile instanceof UploadedFile) {
            $this->addFlash('error', 'La imagen es obligatoria.');
            return $this->redirectToRoute('admin_artworks_new');
        }

        $this->createArtworkService->execute(CreateArtworkCommand::create(
            title:       $request->request->getString('title'),
            description: $request->request->getString('description') ?: null,
            technique:   $request->request->getString('technique'),
            dimensions:  $request->request->getString('dimensions'),
            year:        $request->request->getInt('year'),
            price:       $this->parsePrice($request->request->getString('price')),
            imageFile:   $imageFile,
            shopUrl:     $request->request->getString('shopUrl') ?: null,
            isAvailable: $request->request->getBoolean('isAvailable'),
            categoryIds: $request->request->all('categoryIds'),
            tagIds:      $request->request->all('tagIds'),
        ));

        $this->addFlash('success', 'Obra creada correctamente.');

        return $this->redirectToRoute('admin_artworks');
    }

    #[Route('/{id}/edit', name: 'admin_artworks_edit', methods: ['GET'])]
    public function edit(string $id): Response
    {
        $artwork = $this->artworkRepository->findById(ArtworkId::create($id));

        if ($artwork === null) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/artwork/form.html.twig', [
            'artwork'    => $artwork,
            'categories' => $this->categoryRepository->findAll(),
            'tags'       => $this->tagRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'admin_artworks_update', methods: ['POST'])]
    public function update(string $id, Request $request): Response
    {
        $this->updateArtworkService->execute(UpdateArtworkCommand::create(
            id:          $id,
            title:       $request->request->getString('title'),
            description: $request->request->getString('description') ?: null,
            technique:   $request->request->getString('technique'),
            dimensions:  $request->request->getString('dimensions'),
            year:        $request->request->getInt('year'),
            price:       $this->parsePrice($request->request->getString('price')),
            imageFile:   $request->files->get('imageFile'),
            shopUrl:     $request->request->getString('shopUrl') ?: null,
            isAvailable: $request->request->getBoolean('isAvailable'),
            categoryIds: $request->request->all('categoryIds'),
            tagIds:      $request->request->all('tagIds'),
        ));

        $this->addFlash('success', 'Obra actualizada correctamente.');

        return $this->redirectToRoute('admin_artworks');
    }

    #[Route('/{id}/delete', name: 'admin_artworks_delete', methods: ['POST'])]
    public function delete(string $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_artwork_' . $id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $this->deleteArtworkService->execute(DeleteArtworkCommand::create($id));

        $this->addFlash('success', 'Obra eliminada correctamente.');

        return $this->redirectToRoute('admin_artworks');
    }

    #[Route('/reorder', name: 'admin_artworks_reorder', methods: ['POST'])]
    public function reorder(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ids  = $data['ids'] ?? [];

        $this->reorderArtworksService->execute(ReorderArtworksCommand::create($ids));

        return $this->json(['success' => true]);
    }

    private function parsePrice(string $value): ?float
    {
        $value = trim($value);

        return $value !== '' ? (float) $value : null;
    }
}
