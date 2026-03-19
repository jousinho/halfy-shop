<?php

declare(strict_types=1);

namespace App\Domain\Shared\Event;

abstract class DomainEvent
{
    private readonly \DateTimeImmutable $occurredOn;

    private function __construct(private readonly string $aggregateId)
    {
        $this->occurredOn = new \DateTimeImmutable();
    }

    public static function create(string $aggregateId): static
    {
        return new static($aggregateId);
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
