<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Admin;

use App\Application\Category\Create\CreateCategoryCommand;
use App\Application\Category\Create\CreateCategoryService;
use App\Application\Category\Delete\DeleteCategoryCommand;
use App\Application\Category\Delete\DeleteCategoryService;
use App\Application\Category\Update\UpdateCategoryCommand;
use App\Application\Category\Update\UpdateCategoryService;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/categories')]
final class AdminCategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly CreateCategoryService $createCategoryService,
        private readonly UpdateCategoryService $updateCategoryService,
        private readonly DeleteCategoryService $deleteCategoryService,
    ) {}

    #[Route('', name: 'admin_categories', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/category/index.html.twig', [
            'categories' => $this->categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_categories_new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('admin/category/form.html.twig', ['category' => null]);
    }

    #[Route('', name: 'admin_categories_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $this->createCategoryService->execute(CreateCategoryCommand::create(
            name:      $request->request->getString('name'),
            slug:      $request->request->getString('slug'),
            sortOrder: $request->request->getInt('sortOrder'),
        ));

        $this->addFlash('success', 'Categoría creada correctamente.');

        return $this->redirectToRoute('admin_categories');
    }

    #[Route('/{id}/edit', name: 'admin_categories_edit', methods: ['GET'])]
    public function edit(string $id): Response
    {
        $category = $this->categoryRepository->findById(CategoryId::create($id));

        if ($category === null) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/category/form.html.twig', ['category' => $category]);
    }

    #[Route('/{id}', name: 'admin_categories_update', methods: ['POST'])]
    public function update(string $id, Request $request): Response
    {
        $this->updateCategoryService->execute(UpdateCategoryCommand::create(
            id:        $id,
            name:      $request->request->getString('name'),
            slug:      $request->request->getString('slug'),
            sortOrder: $request->request->getInt('sortOrder'),
        ));

        $this->addFlash('success', 'Categoría actualizada correctamente.');

        return $this->redirectToRoute('admin_categories');
    }

    #[Route('/{id}/delete', name: 'admin_categories_delete', methods: ['POST'])]
    public function delete(string $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_category_' . $id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $this->deleteCategoryService->execute(DeleteCategoryCommand::create($id));

        $this->addFlash('success', 'Categoría eliminada correctamente.');

        return $this->redirectToRoute('admin_categories');
    }
}
