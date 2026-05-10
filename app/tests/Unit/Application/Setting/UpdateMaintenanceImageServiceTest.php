<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Setting;

use App\Application\Setting\UpdateMaintenanceImage\UpdateMaintenanceImageCommand;
use App\Application\Setting\UpdateMaintenanceImage\UpdateMaintenanceImageService;
use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class UpdateMaintenanceImageServiceTest extends TestCase
{
    private SettingRepository&MockObject $settingRepository;
    private UpdateMaintenanceImageService $service;

    protected function setUp(): void
    {
        $this->settingRepository = $this->createMock(SettingRepository::class);
        $this->service           = new UpdateMaintenanceImageService($this->settingRepository);
    }

    public function test_execute__when_setting_not_found__should_create_and_save(): void
    {
        $this->settingRepository->method('findByKey')->willReturn(null);
        $this->settingRepository->expects($this->once())->method('save');

        $this->service->execute(new UpdateMaintenanceImageCommand('maintenance_abc123.jpg'));
    }

    public function test_execute__when_setting_exists__should_update_filename(): void
    {
        $setting = Setting::create('maintenance_image', 'old_image.jpg');
        $this->settingRepository->method('findByKey')->willReturn($setting);

        $this->service->execute(new UpdateMaintenanceImageCommand('new_image.jpg'));

        $this->assertSame('new_image.jpg', $setting->value());
    }

    public function test_execute__when_setting_exists__should_save(): void
    {
        $setting = Setting::create('maintenance_image', 'old_image.jpg');
        $this->settingRepository->method('findByKey')->willReturn($setting);
        $this->settingRepository->expects($this->once())->method('save')->with($setting);

        $this->service->execute(new UpdateMaintenanceImageCommand('new_image.jpg'));
    }
}
