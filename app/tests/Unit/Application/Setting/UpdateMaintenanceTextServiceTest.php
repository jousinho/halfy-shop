<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Setting;

use App\Application\Setting\UpdateMaintenanceText\UpdateMaintenanceTextCommand;
use App\Application\Setting\UpdateMaintenanceText\UpdateMaintenanceTextService;
use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class UpdateMaintenanceTextServiceTest extends TestCase
{
    private SettingRepository&MockObject $settingRepository;
    private UpdateMaintenanceTextService $service;

    protected function setUp(): void
    {
        $this->settingRepository = $this->createMock(SettingRepository::class);
        $this->service           = new UpdateMaintenanceTextService($this->settingRepository);
    }

    public function test_execute__when_setting_not_found__should_create_and_save(): void
    {
        $this->settingRepository->method('findByKey')->willReturn(null);
        $this->settingRepository->expects($this->once())->method('save');

        $this->service->execute(new UpdateMaintenanceTextCommand('Nuevo texto'));
    }

    public function test_execute__when_setting_exists__should_update_text(): void
    {
        $setting = Setting::create('maintenance_text', 'Texto antiguo');
        $this->settingRepository->method('findByKey')->willReturn($setting);

        $this->service->execute(new UpdateMaintenanceTextCommand('Texto nuevo'));

        $this->assertSame('Texto nuevo', $setting->value());
    }

    public function test_execute__when_setting_exists__should_save(): void
    {
        $setting = Setting::create('maintenance_text', 'Texto antiguo');
        $this->settingRepository->method('findByKey')->willReturn($setting);
        $this->settingRepository->expects($this->once())->method('save')->with($setting);

        $this->service->execute(new UpdateMaintenanceTextCommand('Texto nuevo'));
    }
}
