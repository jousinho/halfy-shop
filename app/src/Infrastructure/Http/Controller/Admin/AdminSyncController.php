<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Admin;

use App\Application\Sync\SyncWithBigCartelService;
use App\Domain\Sync\Repository\SyncLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/sync')]
final class AdminSyncController extends AbstractController
{
    public function __construct(
        private readonly SyncLogRepository $syncLogRepository,
        private readonly SyncWithBigCartelService $syncService,
    ) {}

    #[Route('', name: 'admin_sync', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/sync/index.html.twig', [
            'lastSync' => $this->syncLogRepository->findLatest(),
        ]);
    }

    #[Route('', name: 'admin_sync_run', methods: ['POST'])]
    public function sync(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('run_sync', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $log = $this->syncService->execute();

        $this->addFlash('success', sprintf(
            'Sincronización completada: %d creadas, %d actualizadas, %d sin cambios.',
            $log->created(),
            $log->updated(),
            $log->unchanged(),
        ));

        return $this->redirectToRoute('admin_sync');
    }
}
