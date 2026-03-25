<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application;

use App\Application\Setting\GetActiveTheme\GetActiveThemeService;
use App\Application\Setting\UpdateActiveTheme\UpdateActiveThemeCommand;
use App\Application\Setting\UpdateActiveTheme\UpdateActiveThemeService;
use App\Domain\Setting\ValueObject\Theme;
use App\Tests\Integration\IntegrationTestCase;

final class UpdateActiveThemeServiceIntegrationTest extends IntegrationTestCase
{
    private UpdateActiveThemeService $updateService;
    private GetActiveThemeService $getService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->updateService = $this->getService(UpdateActiveThemeService::class);
        $this->getService    = $this->getService(GetActiveThemeService::class);
    }

    public function test_execute__when_setting_exists__should_update_active_theme(): void
    {
        $this->updateService->execute(new UpdateActiveThemeCommand(Theme::Default));

        $result = $this->getService->execute();

        $this->assertSame(Theme::Default, $result);
    }

    public function test_get_active_theme__when_no_setting_in_db__should_return_default(): void
    {
        $result = $this->getService->execute();

        $this->assertSame(Theme::Default, $result);
    }
}
