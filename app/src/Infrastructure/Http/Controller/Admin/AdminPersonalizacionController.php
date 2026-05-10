<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Admin;

use App\Application\Setting\GetMaintenanceImage\GetMaintenanceImageService;
use App\Application\Setting\GetMaintenanceMode\GetMaintenanceModeService;
use App\Application\Setting\GetMaintenanceText\GetMaintenanceTextService;
use App\Application\Setting\UpdateMaintenanceText\UpdateMaintenanceTextCommand;
use App\Application\Setting\UpdateMaintenanceText\UpdateMaintenanceTextService;
use App\Application\Setting\GetSiteFavicon\GetSiteFaviconService;
use App\Application\Setting\GetSiteInstagram\GetSiteInstagramService;
use App\Application\Setting\GetSiteLogo\GetSiteLogoService;
use App\Application\Setting\UpdateMaintenanceImage\UpdateMaintenanceImageCommand;
use App\Application\Setting\UpdateMaintenanceImage\UpdateMaintenanceImageService;
use App\Application\Setting\UpdateMaintenanceMode\UpdateMaintenanceModeCommand;
use App\Application\Setting\UpdateMaintenanceMode\UpdateMaintenanceModeService;
use App\Application\Setting\UpdateSiteFavicon\UpdateSiteFaviconCommand;
use App\Application\Setting\UpdateSiteFavicon\UpdateSiteFaviconService;
use App\Application\Setting\UpdateSiteInstagram\UpdateSiteInstagramCommand;
use App\Application\Setting\UpdateSiteInstagram\UpdateSiteInstagramService;
use App\Application\Setting\UpdateSiteLogo\UpdateSiteLogoCommand;
use App\Application\Setting\UpdateSiteLogo\UpdateSiteLogoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/personalizacion')]
final class AdminPersonalizacionController extends AbstractController
{
    public function __construct(
        private readonly GetSiteLogoService $getSiteLogoService,
        private readonly UpdateSiteLogoService $updateSiteLogoService,
        private readonly GetSiteFaviconService $getSiteFaviconService,
        private readonly UpdateSiteFaviconService $updateSiteFaviconService,
        private readonly GetSiteInstagramService $getSiteInstagramService,
        private readonly UpdateSiteInstagramService $updateSiteInstagramService,
        private readonly GetMaintenanceModeService $getMaintenanceModeService,
        private readonly UpdateMaintenanceModeService $updateMaintenanceModeService,
        private readonly GetMaintenanceImageService $getMaintenanceImageService,
        private readonly UpdateMaintenanceImageService $updateMaintenanceImageService,
        private readonly GetMaintenanceTextService $getMaintenanceTextService,
        private readonly UpdateMaintenanceTextService $updateMaintenanceTextService,
        private readonly string $uploadsDir,
    ) {}

    #[Route('', name: 'admin_personalizacion', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/personalizacion/index.html.twig', [
            'siteLogo'         => $this->getSiteLogoService->execute(),
            'siteFavicon'      => $this->getSiteFaviconService->execute(),
            'siteInstagram'    => $this->getSiteInstagramService->execute(),
            'maintenanceMode'  => $this->getMaintenanceModeService->execute(),
            'maintenanceImage' => $this->getMaintenanceImageService->execute(),
            'maintenanceText'  => $this->getMaintenanceTextService->execute(),
        ]);
    }

    #[Route('/logo', name: 'admin_personalizacion_logo', methods: ['POST'])]
    public function updateLogo(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('update_logo', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $file = $request->files->get('logo');

        if (!$file instanceof UploadedFile) {
            $this->addFlash('error', 'Selecciona una imagen.');
            return $this->redirectToRoute('admin_personalizacion');
        }

        $this->updateSiteLogoService->execute(new UpdateSiteLogoCommand($this->storeFile($file)));
        $this->addFlash('success', 'Logo actualizado correctamente.');

        return $this->redirectToRoute('admin_personalizacion');
    }

    #[Route('/favicon', name: 'admin_personalizacion_favicon', methods: ['POST'])]
    public function updateFavicon(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('update_favicon', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $file = $request->files->get('favicon');

        if (!$file instanceof UploadedFile) {
            $this->addFlash('error', 'Selecciona una imagen.');
            return $this->redirectToRoute('admin_personalizacion');
        }

        $this->updateSiteFaviconService->execute(new UpdateSiteFaviconCommand($this->storeFavicon($file)));
        $this->addFlash('success', 'Favicon actualizado correctamente.');

        return $this->redirectToRoute('admin_personalizacion');
    }

    #[Route('/instagram', name: 'admin_personalizacion_instagram', methods: ['POST'])]
    public function updateInstagram(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('update_instagram', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $url = trim($request->request->getString('instagram_url'));
        $this->updateSiteInstagramService->execute(new UpdateSiteInstagramCommand($url));
        $this->addFlash('success', 'Instagram actualizado correctamente.');

        return $this->redirectToRoute('admin_personalizacion');
    }

    #[Route('/maintenance-text', name: 'admin_personalizacion_maintenance_text', methods: ['POST'])]
    public function updateMaintenanceText(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('update_maintenance_text', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $text = $request->request->getString('maintenance_text');
        $this->updateMaintenanceTextService->execute(new UpdateMaintenanceTextCommand($text));
        $this->addFlash('success', 'Texto actualizado correctamente.');

        return $this->redirectToRoute('admin_personalizacion');
    }

    #[Route('/maintenance', name: 'admin_personalizacion_maintenance', methods: ['POST'])]
    public function updateMaintenance(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('update_maintenance', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $enabled = $request->request->getBoolean('maintenance_mode');
        $this->updateMaintenanceModeService->execute(new UpdateMaintenanceModeCommand($enabled));
        $this->addFlash('success', $enabled ? 'Modo mantenimiento activado.' : 'Modo mantenimiento desactivado.');

        return $this->redirectToRoute('admin_personalizacion');
    }

    #[Route('/maintenance-image', name: 'admin_personalizacion_maintenance_image', methods: ['POST'])]
    public function updateMaintenanceImage(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('update_maintenance_image', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $file = $request->files->get('maintenance_image');

        if (!$file instanceof UploadedFile) {
            $this->addFlash('error', 'Selecciona una imagen.');
            return $this->redirectToRoute('admin_personalizacion');
        }

        $this->updateMaintenanceImageService->execute(new UpdateMaintenanceImageCommand($this->storeMaintenanceImage($file)));
        $this->addFlash('success', 'Imagen actualizada correctamente.');

        return $this->redirectToRoute('admin_personalizacion');
    }

    private function storeFile(UploadedFile $file): string
    {
        $extension = $file->guessExtension() ?? 'jpg';
        $filename  = uniqid('logo_', true) . '.' . $extension;
        $dir       = $this->uploadsDir . '/logo';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file->move($dir, $filename);

        return $filename;
    }

    private function storeFavicon(UploadedFile $file): string
    {
        $extension = $file->guessExtension() ?? 'png';
        $filename  = 'favicon.' . $extension;
        $dir       = $this->uploadsDir . '/favicon';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file->move($dir, $filename);

        return $filename;
    }

    private function storeMaintenanceImage(UploadedFile $file): string
    {
        $extension = $file->guessExtension() ?? 'jpg';
        $filename  = uniqid('maintenance_', true) . '.' . $extension;
        $dir       = $this->uploadsDir . '/maintenance';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file->move($dir, $filename);

        return $filename;
    }
}
