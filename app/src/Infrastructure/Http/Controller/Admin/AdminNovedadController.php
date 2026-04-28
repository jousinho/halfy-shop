<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Admin;

use App\Application\Novedad\Create\CreateNovedadCommand;
use App\Application\Novedad\Create\CreateNovedadService;
use App\Application\Novedad\Delete\DeleteNovedadCommand;
use App\Application\Novedad\Delete\DeleteNovedadService;
use App\Application\Novedad\Update\UpdateNovedadCommand;
use App\Application\Novedad\Update\UpdateNovedadService;
use App\Domain\Novedad\Repository\NovedadRepository;
use App\Domain\Novedad\ValueObject\NovedadId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/novedades')]
final class AdminNovedadController extends AbstractController
{
    public function __construct(
        private readonly NovedadRepository $novedadRepository,
        private readonly CreateNovedadService $createNovedadService,
        private readonly UpdateNovedadService $updateNovedadService,
        private readonly DeleteNovedadService $deleteNovedadService,
    ) {}

    #[Route('', name: 'admin_novedades', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/novedad/index.html.twig', [
            'novedades' => $this->novedadRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_novedades_new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('admin/novedad/form.html.twig', [
            'novedad' => null,
        ]);
    }

    #[Route('', name: 'admin_novedades_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $imagenFile = $request->files->get('imagenFile');

        try {
            $this->createNovedadService->execute(CreateNovedadCommand::create(
                titulo:       $request->request->getString('titulo'),
                tituloEn:     $request->request->getString('tituloEn') ?: null,
                contenido:    $request->request->getString('contenido') ?: null,
                contenidoEn:  $request->request->getString('contenidoEn') ?: null,
                tipo:         $request->request->getString('tipo'),
                fecha:        $request->request->getString('fecha'),
                fechaFin:     $request->request->getString('fechaFin') ?: null,
                imagenFile:   $imagenFile instanceof UploadedFile ? $imagenFile : null,
                lugar:        $request->request->getString('lugar') ?: null,
                url:          $request->request->getString('url') ?: null,
                videoYoutube: $request->request->getString('videoYoutube') ?: null,
                videoReel:    $request->request->getString('videoReel') ?: null,
                publicado:    $request->request->getBoolean('publicado'),
            ));
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_novedades_new');
        }

        $this->addFlash('success', 'Novedad creada correctamente.');

        return $this->redirectToRoute('admin_novedades');
    }

    #[Route('/{id}/edit', name: 'admin_novedades_edit', methods: ['GET'])]
    public function edit(string $id): Response
    {
        $novedad = $this->novedadRepository->findById(NovedadId::create($id));

        if ($novedad === null) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/novedad/form.html.twig', [
            'novedad' => $novedad,
        ]);
    }

    #[Route('/{id}', name: 'admin_novedades_update', methods: ['POST'])]
    public function update(string $id, Request $request): Response
    {
        $imagenFile = $request->files->get('imagenFile');

        try {
            $this->updateNovedadService->execute(UpdateNovedadCommand::create(
                id:           $id,
                titulo:       $request->request->getString('titulo'),
                tituloEn:     $request->request->getString('tituloEn') ?: null,
                contenido:    $request->request->getString('contenido') ?: null,
                contenidoEn:  $request->request->getString('contenidoEn') ?: null,
                tipo:         $request->request->getString('tipo'),
                fecha:        $request->request->getString('fecha'),
                fechaFin:     $request->request->getString('fechaFin') ?: null,
                imagenFile:   $imagenFile instanceof UploadedFile ? $imagenFile : null,
                lugar:        $request->request->getString('lugar') ?: null,
                url:          $request->request->getString('url') ?: null,
                videoYoutube: $request->request->getString('videoYoutube') ?: null,
                videoReel:    $request->request->getString('videoReel') ?: null,
                publicado:    $request->request->getBoolean('publicado'),
            ));
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_novedades_edit', ['id' => $id]);
        }

        $this->addFlash('success', 'Novedad actualizada correctamente.');

        return $this->redirectToRoute('admin_novedades');
    }

    #[Route('/{id}/delete', name: 'admin_novedades_delete', methods: ['POST'])]
    public function delete(string $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_novedad_' . $id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('CSRF token inválido.');
        }

        $this->deleteNovedadService->execute(DeleteNovedadCommand::create($id));

        $this->addFlash('success', 'Novedad eliminada correctamente.');

        return $this->redirectToRoute('admin_novedades');
    }
}
