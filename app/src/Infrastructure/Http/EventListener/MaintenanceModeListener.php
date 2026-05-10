<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\EventListener;

use App\Application\Setting\GetMaintenanceImage\GetMaintenanceImageService;
use App\Application\Setting\GetMaintenanceMode\GetMaintenanceModeService;
use App\Application\Setting\GetMaintenanceText\GetMaintenanceTextService;
use App\Application\Setting\GetSiteLogo\GetSiteLogoService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 7)]
final class MaintenanceModeListener
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly Environment $twig,
        private readonly GetMaintenanceModeService $getMaintenanceModeService,
        private readonly GetMaintenanceImageService $getMaintenanceImageService,
        private readonly GetMaintenanceTextService $getMaintenanceTextService,
        private readonly GetSiteLogoService $getSiteLogoService,
    ) {}

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (str_starts_with($event->getRequest()->getPathInfo(), '/admin')) {
            return;
        }

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        if (!$this->getMaintenanceModeService->execute()) {
            return;
        }

        $html = $this->twig->render('public/under_construction.html.twig', [
            'maintenanceImage' => $this->getMaintenanceImageService->execute(),
            'maintenanceText'  => $this->getMaintenanceTextService->execute(),
            'siteLogo'         => $this->getSiteLogoService->execute(),
        ]);
        $event->setResponse(new Response($html, Response::HTTP_SERVICE_UNAVAILABLE));
    }
}
