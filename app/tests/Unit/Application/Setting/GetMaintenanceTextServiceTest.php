<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Setting;

use App\Application\Setting\GetMaintenanceText\GetMaintenanceTextService;
use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class GetMaintenanceTextServiceTest extends TestCase
{
    private SettingRepository&MockObject $settingRepository;
    private GetMaintenanceTextService $service;

    protected function setUp(): void
    {
        $this->settingRepository = $this->createMock(SettingRepository::class);
        $this->service           = new GetMaintenanceTextService($this->settingRepository);
    }

    public function test_execute__when_setting_not_found__should_return_empty_string(): void
    {
        $this->settingRepository->method('findByKey')->willReturn(null);

        $this->assertSame('', $this->service->execute());
    }

    public function test_execute__when_setting_exists__should_return_text(): void
    {
        $this->settingRepository->method('findByKey')->willReturn(
            Setting::create('maintenance_text', 'Hola, estoy construyendo mi nueva web.')
        );

        $this->assertSame('Hola, estoy construyendo mi nueva web.', $this->service->execute());
    }
}
