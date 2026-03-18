<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Public;

use App\Domain\About\Repository\AboutPageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    public function __construct(
        private readonly AboutPageRepository $aboutPageRepository,
        private readonly string $contactEmail,
    ) {}

    #[Route('/sobre-mi/', name: 'about')]
    public function index(): Response
    {
        return $this->render('public/about/index.html.twig', [
            'page'         => $this->aboutPageRepository->findPage(),
            'contactEmail' => $this->contactEmail,
        ]);
    }
}
