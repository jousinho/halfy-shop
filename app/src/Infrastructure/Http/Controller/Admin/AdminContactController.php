<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Admin;

use App\Application\Setting\GetSiteContactEmail\GetSiteContactEmailService;
use App\Application\Setting\GetSiteContactImage\GetSiteContactImageService;
use App\Application\Setting\GetSiteInstagram\GetSiteInstagramService;
use App\Application\Setting\UpdateSiteContactEmail\UpdateSiteContactEmailCommand;
use App\Application\Setting\UpdateSiteContactEmail\UpdateSiteContactEmailService;
use App\Application\Setting\UpdateSiteContactImage\UpdateSiteContactImageCommand;
use App\Application\Setting\UpdateSiteContactImage\UpdateSiteContactImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/contacto')]
final class AdminContactController extends AbstractController
{
    public function __construct(
        private readonly GetSiteContactImageService $getSiteContactImageService,
        private readonly UpdateSiteContactImageService $updateSiteContactImageService,
        private readonly GetSiteContactEmailService $getSiteContactEmailService,
        private readonly UpdateSiteContactEmailService $updateSiteContactEmailService,
        private readonly GetSiteInstagramService $getSiteInstagramService,
        private readonly string $uploadsDir,
    ) {}

    #[Route('', name: 'admin_contact', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/contact/index.html.twig', [
            'contactImage'   => $this->getSiteContactImageService->execute(),
            'contactEmail'   => $this->getSiteContactEmailService->execute(),
            'siteInstagram'  => $this->getSiteInstagramService->execute(),
        ]);
    }

    #[Route('/imagen', name: 'admin_contact_image', methods: ['POST'])]
    public function updateImage(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('update_contact_image', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $file = $request->files->get('contact_image');

        if (!$file instanceof UploadedFile) {
            $this->addFlash('error', 'Selecciona una imagen.');
            return $this->redirectToRoute('admin_contact');
        }

        $this->updateSiteContactImageService->execute(
            new UpdateSiteContactImageCommand($this->storeFile($file))
        );
        $this->addFlash('success', 'Imagen actualizada correctamente.');

        return $this->redirectToRoute('admin_contact');
    }

    #[Route('/email', name: 'admin_contact_email', methods: ['POST'])]
    public function updateEmail(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('update_contact_email', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $email = trim($request->request->getString('contact_email'));
        $this->updateSiteContactEmailService->execute(new UpdateSiteContactEmailCommand($email));
        $this->addFlash('success', 'Email actualizado correctamente.');

        return $this->redirectToRoute('admin_contact');
    }

    private function storeFile(UploadedFile $file): string
    {
        $extension = $file->guessExtension() ?? 'jpg';
        $filename  = uniqid('contact_', true) . '.' . $extension;
        $dir       = $this->uploadsDir . '/contact';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file->move($dir, $filename);

        return $filename;
    }
}
