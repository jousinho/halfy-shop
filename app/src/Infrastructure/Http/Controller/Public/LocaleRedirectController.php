<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class LocaleRedirectController extends AbstractController
{
    public function index(Request $request): RedirectResponse
    {
        $locale = $request->getPreferredLanguage(['es', 'en']) ?? 'es';

        return $this->redirectToRoute('home', ['_locale' => $locale], 301);
    }
}
