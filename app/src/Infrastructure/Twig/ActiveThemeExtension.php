<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig;

use App\Application\Setting\GetSiteContactEmail\GetSiteContactEmailService;
use App\Application\Setting\GetSiteContactImage\GetSiteContactImageService;
use App\Application\Setting\GetSiteInstagram\GetSiteInstagramService;
use App\Application\Setting\GetSiteFavicon\GetSiteFaviconService;
use App\Application\Setting\GetSiteLogo\GetSiteLogoService;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class ActiveThemeExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly GetSiteLogoService $getSiteLogoService,
        private readonly GetSiteFaviconService $getSiteFaviconService,
        private readonly GetSiteInstagramService $getSiteInstagramService,
        private readonly GetSiteContactImageService $getSiteContactImageService,
        private readonly GetSiteContactEmailService $getSiteContactEmailService,
    ) {}

    public function getGlobals(): array
    {
        try { $siteLogo         = $this->getSiteLogoService->execute();         } catch (\Exception) { $siteLogo         = null; }
        try { $siteFavicon      = $this->getSiteFaviconService->execute();      } catch (\Exception) { $siteFavicon      = null; }
        try { $siteInstagram    = $this->getSiteInstagramService->execute();    } catch (\Exception) { $siteInstagram    = null; }
        try { $siteContactImage = $this->getSiteContactImageService->execute(); } catch (\Exception) { $siteContactImage = null; }
        try { $siteContactEmail = $this->getSiteContactEmailService->execute(); } catch (\Exception) { $siteContactEmail = null; }

        return [
            'siteLogo'         => $siteLogo,
            'siteFavicon'      => $siteFavicon,
            'siteInstagram'    => $siteInstagram,
            'siteContactImage' => $siteContactImage,
            'siteContactEmail' => $siteContactEmail,
        ];
    }
}
