<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\EventListener;

use App\Application\Setting\GetMaintenanceImage\GetMaintenanceImageService;
use App\Application\Setting\GetMaintenanceMode\GetMaintenanceModeService;
use App\Application\Setting\GetMaintenanceText\GetMaintenanceTextService;
use App\Application\Setting\GetSiteLogo\GetSiteLogoService;
use App\Domain\Setting\Entity\Setting;
use App\Domain\Setting\Repository\SettingRepository;
use App\Infrastructure\Http\EventListener\MaintenanceModeListener;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

#[AllowMockObjectsWithoutExpectations]
final class MaintenanceModeListenerTest extends TestCase
{
    private AuthorizationCheckerInterface&MockObject $authorizationChecker;
    private Environment&MockObject $twig;
    private SettingRepository&MockObject $settingRepository;
    private SettingRepository&MockObject $logoRepository;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->twig                 = $this->createMock(Environment::class);
        $this->settingRepository    = $this->createMock(SettingRepository::class);
        $this->logoRepository       = $this->createMock(SettingRepository::class);
    }

    public function test_invoke__when_maintenance_active__should_render_template_with_all_variables(): void
    {
        $this->configureSettings('1', 'maintenance_abc.jpg', 'Texto de prueba');
        $this->logoRepository->method('findByKey')->willReturn(Setting::create('site_logo', 'logo.png'));
        $this->authorizationChecker->method('isGranted')->willReturn(false);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('public/under_construction.html.twig', [
                'maintenanceImage' => 'maintenance_abc.jpg',
                'maintenanceText'  => 'Texto de prueba',
                'siteLogo'         => 'logo.png',
            ])
            ->willReturn('<html></html>');

        $this->buildListener()->__invoke($this->buildEvent('/'));
    }

    public function test_invoke__when_maintenance_active_without_image__should_render_with_null_image(): void
    {
        $this->configureSettings('1', '', 'Texto');
        $this->logoRepository->method('findByKey')->willReturn(null);
        $this->authorizationChecker->method('isGranted')->willReturn(false);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('public/under_construction.html.twig', [
                'maintenanceImage' => null,
                'maintenanceText'  => 'Texto',
                'siteLogo'         => null,
            ])
            ->willReturn('<html></html>');

        $this->buildListener()->__invoke($this->buildEvent('/'));
    }

    public function test_invoke__when_maintenance_inactive__should_not_render(): void
    {
        $this->configureSettings('0', '', '');
        $this->twig->expects($this->never())->method('render');

        $this->buildListener()->__invoke($this->buildEvent('/'));
    }

    public function test_invoke__when_admin_route__should_not_render(): void
    {
        $this->twig->expects($this->never())->method('render');

        $this->buildListener()->__invoke($this->buildEvent('/admin/artworks'));
    }

    public function test_invoke__when_user_is_admin__should_not_render(): void
    {
        $this->configureSettings('1', '', '');
        $this->authorizationChecker->method('isGranted')->willReturn(true);
        $this->twig->expects($this->never())->method('render');

        $this->buildListener()->__invoke($this->buildEvent('/'));
    }

    public function test_invoke__when_maintenance_active__should_return_503(): void
    {
        $this->configureSettings('1', '', '');
        $this->logoRepository->method('findByKey')->willReturn(null);
        $this->authorizationChecker->method('isGranted')->willReturn(false);
        $this->twig->method('render')->willReturn('<html></html>');

        $event = $this->buildEvent('/');
        $this->buildListener()->__invoke($event);

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $event->getResponse()->getStatusCode());
    }

    private function configureSettings(string $maintenanceMode, string $maintenanceImage, string $maintenanceText): void
    {
        $this->settingRepository->method('findByKey')->willReturnCallback(
            function (string $key) use ($maintenanceMode, $maintenanceImage, $maintenanceText): ?Setting {
                return match ($key) {
                    'maintenance_mode'  => Setting::create('maintenance_mode', $maintenanceMode),
                    'maintenance_image' => Setting::create('maintenance_image', $maintenanceImage),
                    'maintenance_text'  => Setting::create('maintenance_text', $maintenanceText),
                    default             => null,
                };
            }
        );
    }

    private function buildListener(): MaintenanceModeListener
    {
        return new MaintenanceModeListener(
            $this->authorizationChecker,
            $this->twig,
            new GetMaintenanceModeService($this->settingRepository),
            new GetMaintenanceImageService($this->settingRepository),
            new GetMaintenanceTextService($this->settingRepository),
            new GetSiteLogoService($this->logoRepository),
        );
    }

    private function buildEvent(string $path): RequestEvent
    {
        $kernel  = $this->createMock(HttpKernelInterface::class);
        $request = Request::create($path);

        return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
    }
}
