<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Setting;

use App\Application\Setting\GetMaintenanceImage\GetMaintenanceImageService;
use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class GetMaintenanceImageServiceTest extends TestCase
{
    private SettingRepository&MockObject $settingRepository;
    private GetMaintenanceImageService $service;

    protected function setUp(): void
    {
        $this->settingRepository = $this->createMock(SettingRepository::class);
        $this->service           = new GetMaintenanceImageService($this->settingRepository);
    }

    public function test_execute__when_setting_not_found__should_return_null(): void
    {
        $this->settingRepository->method('findByKey')->willReturn(null);

        $this->assertNull($this->service->execute());
    }

    public function test_execute__when_setting_is_empty__should_return_null(): void
    {
        $this->settingRepository->method('findByKey')->willReturn(Setting::create('maintenance_image', ''));

        $this->assertNull($this->service->execute());
    }

    public function test_execute__when_setting_has_filename__should_return_filename(): void
    {
        $this->settingRepository->method('findByKey')->willReturn(Setting::create('maintenance_image', 'maintenance_abc123.jpg'));

        $this->assertSame('maintenance_abc123.jpg', $this->service->execute());
    }
}
