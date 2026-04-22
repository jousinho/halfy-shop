<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Public;

use App\Application\Search\SearchQuery;
use App\Application\Search\SearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    public function __construct(private readonly SearchService $searchService) {}

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $term    = $request->query->getString('q');
        $query   = SearchQuery::create($term);
        $results = $this->searchService->search($query, $request->getLocale());

        return $this->render('public/search/results.html.twig', [
            'term'    => $term,
            'results' => $results,
        ]);
    }
}
