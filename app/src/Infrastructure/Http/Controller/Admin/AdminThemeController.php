<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Admin;

use App\Application\Setting\GetActiveTheme\GetActiveThemeService;
use App\Application\Setting\UpdateActiveTheme\UpdateActiveThemeCommand;
use App\Application\Setting\UpdateActiveTheme\UpdateActiveThemeService;
use App\Domain\Setting\ValueObject\Theme;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/tema')]
final class AdminThemeController extends AbstractController
{
    public function __construct(
        private readonly GetActiveThemeService $getActiveThemeService,
        private readonly UpdateActiveThemeService $updateActiveThemeService,
    ) {}

    #[Route('', name: 'admin_theme', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/theme/index.html.twig', [
            'themes'      => Theme::cases(),
            'activeTheme' => $this->getActiveThemeService->execute(),
        ]);
    }

    #[Route('', name: 'admin_theme_update', methods: ['POST'])]
    public function update(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('update_theme', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $themeValue = $request->request->getString('theme');
        $theme      = Theme::tryFrom($themeValue);

        if ($theme === null) {
            $this->addFlash('error', 'Tema no válido.');
            return $this->redirectToRoute('admin_theme');
        }

        $this->updateActiveThemeService->execute(new UpdateActiveThemeCommand($theme));

        $this->addFlash('success', sprintf('Tema "%s" activado correctamente.', $theme->label()));

        return $this->redirectToRoute('admin_theme');
    }
}
