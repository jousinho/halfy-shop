<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Setting;

use App\Application\Setting\GetAnnouncement\GetAnnouncementService;
use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetAnnouncementServiceTest extends TestCase
{
    private SettingRepository&MockObject $settingRepository;
    private GetAnnouncementService $service;

    protected function setUp(): void
    {
        $this->settingRepository = $this->createMock(SettingRepository::class);
        $this->service            = new GetAnnouncementService($this->settingRepository);
    }

    public function test_get_announcement__when_settings_not_found__should_return_defaults(): void
    {
        $this->settingRepository->method('findByKey')->willReturn(null);

        $result = $this->service->execute();

        $this->assertSame(['enabled' => false, 'text' => ''], $result);
    }

    public function test_get_announcement__when_enabled_and_text_set__should_return_them(): void
    {
        $this->settingRepository->method('findByKey')->willReturnMap([
            ['announcement_enabled', Setting::create('announcement_enabled', '1')],
            ['announcement_text', Setting::create('announcement_text', 'Cerrado en agosto')],
        ]);

        $result = $this->service->execute();

        $this->assertSame(['enabled' => true, 'text' => 'Cerrado en agosto'], $result);
    }

    public function test_get_announcement__when_disabled__should_return_enabled_false(): void
    {
        $this->settingRepository->method('findByKey')->willReturnMap([
            ['announcement_enabled', Setting::create('announcement_enabled', '0')],
            ['announcement_text', Setting::create('announcement_text', 'Texto guardado')],
        ]);

        $result = $this->service->execute();

        $this->assertSame(['enabled' => false, 'text' => 'Texto guardado'], $result);
    }
}
