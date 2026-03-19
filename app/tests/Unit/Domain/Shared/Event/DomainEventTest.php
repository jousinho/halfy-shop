<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shared\Event;

use App\Domain\Shared\Event\DomainEvent;
use PHPUnit\Framework\TestCase;

final class ConcreteDomainEvent extends DomainEvent {}

final class DomainEventTest extends TestCase
{
    public function test_create_domain_event__should_store_aggregate_id(): void
    {
        $event = ConcreteDomainEvent::create('some-aggregate-id');

        $this->assertSame('some-aggregate-id', $event->aggregateId());
    }

    public function test_create_domain_event__should_set_occurred_on_to_now(): void
    {
        $before = new \DateTimeImmutable();
        $event  = ConcreteDomainEvent::create('some-aggregate-id');
        $after  = new \DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $event->occurredOn());
        $this->assertLessThanOrEqual($after, $event->occurredOn());
    }
}
