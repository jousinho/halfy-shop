<?php

declare(strict_types=1);

namespace App\Domain\Novedad\Event;

final class NovedadDeleted
{
    private function __construct(
        private readonly string $aggregateId,
        private readonly \DateTimeImmutable $occurredOn,
    ) {}

    public static function create(string $aggregateId): self
    {
        return new self($aggregateId, new \DateTimeImmutable());
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
