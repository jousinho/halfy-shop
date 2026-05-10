<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Setting;

use App\Application\Setting\UpdateMaintenanceMode\UpdateMaintenanceModeCommand;
use App\Application\Setting\UpdateMaintenanceMode\UpdateMaintenanceModeService;
use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class UpdateMaintenanceModeServiceTest extends TestCase
{
    private SettingRepository&MockObject $settingRepository;
    private UpdateMaintenanceModeService $service;

    protected function setUp(): void
    {
        $this->settingRepository = $this->createMock(SettingRepository::class);
        $this->service           = new UpdateMaintenanceModeService($this->settingRepository);
    }

    public function test_execute__when_enabled__should_save_value_one(): void
    {
        $setting = Setting::create('maintenance_mode', '0');
        $this->settingRepository->method('findByKey')->willReturn($setting);

        $this->service->execute(new UpdateMaintenanceModeCommand(true));

        $this->assertSame('1', $setting->value());
    }

    public function test_execute__when_disabled__should_save_value_zero(): void
    {
        $setting = Setting::create('maintenance_mode', '1');
        $this->settingRepository->method('findByKey')->willReturn($setting);

        $this->service->execute(new UpdateMaintenanceModeCommand(false));

        $this->assertSame('0', $setting->value());
    }

    public function test_execute__when_setting_not_found__should_create_and_save(): void
    {
        $this->settingRepository->method('findByKey')->willReturn(null);
        $this->settingRepository->expects($this->once())->method('save');

        $this->service->execute(new UpdateMaintenanceModeCommand(true));
    }

    public function test_execute__when_setting_exists__should_update_and_save(): void
    {
        $setting = Setting::create('maintenance_mode', '0');
        $this->settingRepository->method('findByKey')->willReturn($setting);
        $this->settingRepository->expects($this->once())->method('save')->with($setting);

        $this->service->execute(new UpdateMaintenanceModeCommand(true));
    }
}
