<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Sync\Entity;

use App\Domain\Sync\Entity\SyncLog;
use PHPUnit\Framework\TestCase;

final class SyncLogTest extends TestCase
{
    public function test_create_sync_log__should_store_all_fields_correctly(): void
    {
        $log = $this->buildSyncLog();

        $this->assertSame('some-uuid', $log->id());
        $this->assertSame(3, $log->created());
        $this->assertSame(2, $log->updated());
        $this->assertSame(5, $log->unchanged());
        $this->assertSame("Created: Fluye\nUpdated: Calma", $log->log());
    }

    public function test_create_sync_log__should_set_executed_at_to_now(): void
    {
        $before = new \DateTimeImmutable();
        $log    = $this->buildSyncLog();
        $after  = new \DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $log->executedAt());
        $this->assertLessThanOrEqual($after, $log->executedAt());
    }

    public function test_total__should_sum_created_updated_and_unchanged(): void
    {
        $log = $this->buildSyncLog();

        $this->assertSame(10, $log->total());
    }

    public function test_create_sync_log__when_all_unchanged__should_return_zero_created_and_updated(): void
    {
        $log = SyncLog::create('uuid', 0, 0, 10, 'All unchanged');

        $this->assertSame(0, $log->created());
        $this->assertSame(0, $log->updated());
        $this->assertSame(10, $log->unchanged());
        $this->assertSame(10, $log->total());
    }

    private function buildSyncLog(): SyncLog
    {
        return SyncLog::create(
            id:        'some-uuid',
            created:   3,
            updated:   2,
            unchanged: 5,
            log:       "Created: Fluye\nUpdated: Calma",
        );
    }
}
