<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Setting;

use App\Application\Setting\UpdateAnnouncement\UpdateAnnouncementCommand;
use App\Application\Setting\UpdateAnnouncement\UpdateAnnouncementService;
use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class UpdateAnnouncementServiceTest extends TestCase
{
    private SettingRepository&MockObject $settingRepository;
    private UpdateAnnouncementService $service;

    protected function setUp(): void
    {
        $this->settingRepository = $this->createMock(SettingRepository::class);
        $this->service            = new UpdateAnnouncementService($this->settingRepository);
    }

    public function test_update_announcement__when_settings_not_found__should_create_and_save_both(): void
    {
        $this->settingRepository->method('findByKey')->willReturn(null);
        $this->settingRepository->expects($this->exactly(2))->method('save');

        $this->service->execute(new UpdateAnnouncementCommand(true, 'Cerrado en agosto'));
    }

    public function test_update_announcement__when_settings_exist__should_update_enabled_and_text(): void
    {
        $enabledSetting = Setting::create('announcement_enabled', '0');
        $textSetting    = Setting::create('announcement_text', '');

        $this->settingRepository->method('findByKey')->willReturnMap([
            ['announcement_enabled', $enabledSetting],
            ['announcement_text', $textSetting],
        ]);

        $this->service->execute(new UpdateAnnouncementCommand(true, 'Cerrado en agosto'));

        $this->assertSame('1', $enabledSetting->value());
        $this->assertSame('Cerrado en agosto', $textSetting->value());
    }

    public function test_update_announcement__when_disabled__should_save_value_zero(): void
    {
        $enabledSetting = Setting::create('announcement_enabled', '1');
        $textSetting    = Setting::create('announcement_text', 'Texto previo');

        $this->settingRepository->method('findByKey')->willReturnMap([
            ['announcement_enabled', $enabledSetting],
            ['announcement_text', $textSetting],
        ]);

        $this->service->execute(new UpdateAnnouncementCommand(false, 'Texto previo'));

        $this->assertSame('0', $enabledSetting->value());
    }
}
