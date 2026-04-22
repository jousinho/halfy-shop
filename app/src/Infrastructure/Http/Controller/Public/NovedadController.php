<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Public;

use App\Domain\Novedad\Repository\NovedadRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NovedadController extends AbstractController
{
    public function __construct(
        private readonly NovedadRepository $novedadRepository,
    ) {}

    #[Route('/novedades', name: 'novedades', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('public/novedades/index.html.twig', [
            'novedades' => $this->novedadRepository->findAllPublished(),
        ]);
    }

    #[Route('/novedades/{slug}', name: 'novedad_show', methods: ['GET'])]
    public function show(string $slug): Response
    {
        $novedad = $this->novedadRepository->findBySlug($slug);

        if ($novedad === null) {
            throw $this->createNotFoundException();
        }

        return $this->render('public/novedades/show.html.twig', [
            'novedad' => $novedad,
        ]);
    }
}
