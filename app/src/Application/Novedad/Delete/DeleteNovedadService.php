<?php

declare(strict_types=1);

namespace App\Application\Novedad\Delete;

use App\Domain\Novedad\Repository\NovedadRepository;
use App\Domain\Novedad\ValueObject\NovedadId;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class DeleteNovedadService
{
    public function __construct(
        private readonly NovedadRepository $novedadRepository,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public function execute(DeleteNovedadCommand $command): void
    {
        $novedad = $this->novedadRepository->findById(NovedadId::create($command->id));

        if ($novedad === null) {
            throw new \RuntimeException(sprintf('Novedad "%s" not found.', $command->id));
        }

        $novedad->markAsDeleted();
        $this->novedadRepository->delete($novedad);

        foreach ($novedad->pullDomainEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }
    }
}
