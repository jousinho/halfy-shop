<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig;

use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

final class ThemeAwareTwigLoader implements LoaderInterface
{
    public function __construct(private readonly string $viewsDir) {}

    public function getSourceContext(string $name): Source
    {
        $path = $this->resolveThemePath($name);

        if ($path === null) {
            throw new LoaderError(sprintf('Template "%s" not found in active theme.', $name));
        }

        return new Source((string) file_get_contents($path), $name, $path);
    }

    public function getCacheKey(string $name): string
    {
        $path = $this->resolveThemePath($name);

        if ($path === null) {
            throw new LoaderError(sprintf('Template "%s" not found in active theme.', $name));
        }

        return $path;
    }

    public function isFresh(string $name, int $time): bool
    {
        $path = $this->resolveThemePath($name);

        if ($path === null) {
            throw new LoaderError(sprintf('Template "%s" not found in active theme.', $name));
        }

        return filemtime($path) <= $time;
    }

    public function exists(string $name): bool
    {
        return $this->resolveThemePath($name) !== null;
    }

    private function resolveThemePath(string $name): ?string
    {
        if (!str_starts_with($name, 'public/') || str_starts_with($name, 'public/themes/')) {
            return null;
        }

        $rest = substr($name, strlen('public/'));
        $path = $this->viewsDir . '/public/themes/custom/' . $rest;

        return file_exists($path) ? $path : null;
    }
}
