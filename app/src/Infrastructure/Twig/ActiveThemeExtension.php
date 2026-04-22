<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig;

use App\Application\Setting\GetActiveTheme\GetActiveThemeService;
use App\Application\Setting\GetSiteContactEmail\GetSiteContactEmailService;
use App\Application\Setting\GetSiteContactImage\GetSiteContactImageService;
use App\Application\Setting\GetSiteInstagram\GetSiteInstagramService;
use App\Application\Setting\GetSiteLogo\GetSiteLogoService;
use App\Domain\Setting\ValueObject\Theme;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class ActiveThemeExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly GetActiveThemeService $getActiveThemeService,
        private readonly GetSiteLogoService $getSiteLogoService,
        private readonly GetSiteInstagramService $getSiteInstagramService,
        private readonly GetSiteContactImageService $getSiteContactImageService,
        private readonly GetSiteContactEmailService $getSiteContactEmailService,
    ) {}

    public function getGlobals(): array
    {
        try {
            $theme = $this->getActiveThemeService->execute()->value;
        } catch (\Exception) {
            $theme = Theme::Default->value;
        }

        try { $siteLogo         = $this->getSiteLogoService->execute();         } catch (\Exception) { $siteLogo         = null; }
        try { $siteInstagram    = $this->getSiteInstagramService->execute();    } catch (\Exception) { $siteInstagram    = null; }
        try { $siteContactImage = $this->getSiteContactImageService->execute(); } catch (\Exception) { $siteContactImage = null; }
        try { $siteContactEmail = $this->getSiteContactEmailService->execute(); } catch (\Exception) { $siteContactEmail = null; }

        return [
            'activeTheme'    => $theme,
            'siteLogo'       => $siteLogo,
            'siteInstagram'  => $siteInstagram,
            'siteContactImage' => $siteContactImage,
            'siteContactEmail' => $siteContactEmail,
        ];
    }
}
