<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\EventListener;

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

        $html = $this->twig->render('public/under_construction.html.twig');
        $event->setResponse(new Response($html, Response::HTTP_SERVICE_UNAVAILABLE));
    }
}
