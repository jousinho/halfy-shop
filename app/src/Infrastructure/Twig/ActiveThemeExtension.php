<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig;

use App\Application\Setting\GetActiveTheme\GetActiveThemeService;
use App\Domain\Setting\ValueObject\Theme;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class ActiveThemeExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private readonly GetActiveThemeService $getActiveThemeService) {}

    public function getGlobals(): array
    {
        try {
            $theme = $this->getActiveThemeService->execute()->value;
        } catch (\Exception) {
            $theme = Theme::Default->value;
        }

        return ['activeTheme' => $theme];
    }
}
