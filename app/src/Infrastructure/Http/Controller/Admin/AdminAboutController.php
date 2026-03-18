<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Admin;

use App\Application\About\UpdateAboutCommand;
use App\Application\About\UpdateAboutService;
use App\Domain\About\Repository\AboutPageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/about')]
final class AdminAboutController extends AbstractController
{
    public function __construct(
        private readonly AboutPageRepository $aboutPageRepository,
        private readonly UpdateAboutService $updateAboutService,
    ) {}

    #[Route('', name: 'admin_about', methods: ['GET'])]
    public function edit(): Response
    {
        return $this->render('admin/about/form.html.twig', [
            'page' => $this->aboutPageRepository->findPage(),
        ]);
    }

    #[Route('', name: 'admin_about_update', methods: ['POST'])]
    public function update(Request $request): Response
    {
        $this->updateAboutService->execute(UpdateAboutCommand::create(
            content:     $request->request->getString('content'),
            photoFile:   $request->files->get('photoFile'),
            removePhoto: $request->request->getBoolean('removePhoto'),
        ));

        $this->addFlash('success', 'Página "Sobre mí" actualizada correctamente.');

        return $this->redirectToRoute('admin_about');
    }
}
