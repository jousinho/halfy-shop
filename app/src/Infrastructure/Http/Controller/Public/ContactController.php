<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Public;

use App\Application\Setting\GetSiteContactEmail\GetSiteContactEmailService;
use App\Application\Setting\GetSiteContactImage\GetSiteContactImageService;
use App\Application\Setting\GetSiteInstagram\GetSiteInstagramService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    public function __construct(
        private readonly GetSiteContactImageService $getContactImageService,
        private readonly GetSiteContactEmailService $getContactEmailService,
        private readonly GetSiteInstagramService $getInstagramService,
    ) {}

    #[Route('/contacto/', name: 'contact')]
    public function index(): Response
    {
        return $this->render('public/contact/index.html.twig', [
            'contactImage'   => $this->getContactImageService->execute(),
            'contactEmail'   => $this->getContactEmailService->execute(),
            'contactInstagram' => $this->getInstagramService->execute(),
        ]);
    }
}
