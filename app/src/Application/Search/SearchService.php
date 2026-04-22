<?php

declare(strict_types=1);

namespace App\Application\Search;

use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Novedad\Entity\Novedad;
use App\Domain\Novedad\Repository\NovedadRepository;

final class SearchService
{
    public function __construct(
        private readonly ArtworkRepository $artworkRepository,
        private readonly NovedadRepository $novedadRepository,
    ) {}

    /** @return array{artworks: SearchResultItem[], novedades: SearchResultItem[]} */
    public function search(SearchQuery $query, string $locale): array
    {
        if ($query->isEmpty()) {
            return ['artworks' => [], 'novedades' => []];
        }

        return [
            'artworks'  => $this->mapArtworks($this->artworkRepository->search($query->term), $locale),
            'novedades' => $this->mapNovedades($this->novedadRepository->search($query->term), $locale),
        ];
    }

    /** @param Artwork[] $artworks @return SearchResultItem[] */
    private function mapArtworks(array $artworks, string $locale): array
    {
        return array_map(function (Artwork $a) use ($locale): SearchResultItem {
            return SearchResultItem::fromArtwork(
                title:         $a->titleForLocale($locale),
                subtitle:      $a->techniqueForLocale($locale),
                url:           '/' . $locale . '/',
                imageFilename: $a->imageFilename(),
            );
        }, $artworks);
    }

    /** @param Novedad[] $novedades @return SearchResultItem[] */
    private function mapNovedades(array $novedades, string $locale): array
    {
        return array_map(function (Novedad $n) use ($locale): SearchResultItem {
            return SearchResultItem::fromNovedad(
                title:         $n->tituloForLocale($locale),
                subtitle:      $n->lugar(),
                url:           '/' . $locale . '/novedades/' . $n->slug(),
                imageFilename: $n->imagen(),
            );
        }, $novedades);
    }
}
