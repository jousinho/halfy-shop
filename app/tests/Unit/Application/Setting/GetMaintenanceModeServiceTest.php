<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Setting;

use App\Application\Setting\GetMaintenanceMode\GetMaintenanceModeService;
use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class GetMaintenanceModeServiceTest extends TestCase
{
    private SettingRepository&MockObject $settingRepository;
    private GetMaintenanceModeService $service;

    protected function setUp(): void
    {
        $this->settingRepository = $this->createMock(SettingRepository::class);
        $this->service           = new GetMaintenanceModeService($this->settingRepository);
    }

    public function test_execute__when_setting_not_found__should_return_true(): void
    {
        $this->settingRepository->method('findByKey')->willReturn(null);

        $this->assertTrue($this->service->execute());
    }

    public function test_execute__when_setting_is_enabled__should_return_true(): void
    {
        $this->settingRepository->method('findByKey')->willReturn(Setting::create('maintenance_mode', '1'));

        $this->assertTrue($this->service->execute());
    }

    public function test_execute__when_setting_is_disabled__should_return_false(): void
    {
        $this->settingRepository->method('findByKey')->willReturn(Setting::create('maintenance_mode', '0'));

        $this->assertFalse($this->service->execute());
    }
}
