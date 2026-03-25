<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Twig;

use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;
use App\Infrastructure\Twig\ThemeAwareTwigLoader;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Error\LoaderError;

#[AllowMockObjectsWithoutExpectations]
final class ThemeAwareTwigLoaderTest extends TestCase
{
    private string $viewsDir;
    private SettingRepository&MockObject $settingRepository;

    protected function setUp(): void
    {
        $this->viewsDir          = sys_get_temp_dir() . '/halfyshop_twig_test_' . uniqid();
        $this->settingRepository = $this->createMock(SettingRepository::class);

        mkdir($this->viewsDir . '/public/themes/minimal/home', 0777, true);
        file_put_contents(
            $this->viewsDir . '/public/themes/minimal/home/index.html.twig',
            '<h1>Minimal theme</h1>'
        );
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->viewsDir);
    }

    public function test_exists__when_theme_is_default__should_return_false(): void
    {
        $this->settingReturns('default');
        $loader = $this->buildLoader();

        $this->assertFalse($loader->exists('public/home/index.html.twig'));
    }

    public function test_exists__when_theme_override_exists__should_return_true(): void
    {
        $this->settingReturns('minimal');
        $loader = $this->buildLoader();

        $this->assertTrue($loader->exists('public/home/index.html.twig'));
    }

    public function test_exists__when_theme_override_does_not_exist__should_return_false(): void
    {
        $this->settingReturns('minimal');
        $loader = $this->buildLoader();

        $this->assertFalse($loader->exists('public/about/index.html.twig'));
    }

    public function test_exists__when_template_is_not_public__should_return_false(): void
    {
        $this->settingReturns('minimal');
        $loader = $this->buildLoader();

        $this->assertFalse($loader->exists('admin/layout.html.twig'));
    }

    public function test_exists__when_template_is_inside_themes_folder__should_return_false(): void
    {
        $this->settingReturns('minimal');
        $loader = $this->buildLoader();

        $this->assertFalse($loader->exists('public/themes/minimal/home/index.html.twig'));
    }

    public function test_getSourceContext__when_theme_override_exists__should_return_theme_content(): void
    {
        $this->settingReturns('minimal');
        $loader = $this->buildLoader();

        $source = $loader->getSourceContext('public/home/index.html.twig');

        $this->assertSame('<h1>Minimal theme</h1>', $source->getCode());
    }

    public function test_getSourceContext__when_no_override__should_throw_loader_error(): void
    {
        $this->settingReturns('default');
        $loader = $this->buildLoader();

        $this->expectException(LoaderError::class);

        $loader->getSourceContext('public/home/index.html.twig');
    }

    private function settingReturns(string $themeValue): void
    {
        $setting = Setting::create('active_theme', $themeValue);
        $this->settingRepository->method('findByKey')->willReturn($setting);
    }

    private function buildLoader(): ThemeAwareTwigLoader
    {
        return new ThemeAwareTwigLoader($this->settingRepository, $this->viewsDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}
