<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Feed;

use App\Application\Shared\BigCartelFeedFetcher;

final class RssXmlBigCartelFeedFetcher implements BigCartelFeedFetcher
{
    public function __construct(private readonly DescriptionParser $descriptionParser) {}

    public function fetch(string $feedUrl): array
    {
        $xmlContent = @file_get_contents($feedUrl);

        if ($xmlContent === false) {
            return [];
        }

        $xml = simplexml_load_string($xmlContent);

        if ($xml === false) {
            return [];
        }

        $items = [];

        foreach ($xml->channel->item as $item) {
            $parsed = $this->parseItem($item);

            if ($parsed !== null) {
                $items[] = $parsed;
            }
        }

        return $items;
    }

    private function parseItem(\SimpleXMLElement $item): ?array
    {
        $namespaces = $item->getNamespaces(true);
        $g          = isset($namespaces['g']) ? $item->children($namespaces['g']) : null;

        $shopUrl = $g !== null && isset($g->link) ? (string) $g->link : (string) $item->link;

        if ($shopUrl === '') {
            return null;
        }

        $title       = $g !== null && isset($g->title) ? trim((string) $g->title) : trim((string) $item->title);
        $description = $this->extractDescription($item);
        $parsed      = $this->descriptionParser->parse($description);
        $imageUrl    = $this->extractImageUrl($item, $g);
        $price       = $this->extractPrice($item, $namespaces);

        return [
            'title'       => $title,
            'description' => $description,
            'technique'   => $parsed['technique'],
            'dimensions'  => $parsed['dimensions'],
            'price'       => $price,
            'shopUrl'     => $shopUrl,
            'imageUrl'    => $imageUrl,
            'isAvailable' => $this->extractAvailability($item, $namespaces),
        ];
    }

    private function extractImageUrl(\SimpleXMLElement $item, ?\SimpleXMLElement $g): string
    {
        if ($g !== null && isset($g->image_link)) {
            $url = (string) $g->image_link;
            if ($url !== '') {
                return $url;
            }
        }

        $enclosure = $item->enclosure;
        if ($enclosure !== null) {
            $url = (string) $enclosure->attributes()['url'];
            if ($url !== '') {
                return $url;
            }
        }

        $namespaces = $item->getNamespaces(true);
        if (isset($namespaces['media'])) {
            $media = $item->children($namespaces['media']);
            if (isset($media->content)) {
                return (string) $media->content->attributes()['url'];
            }
        }

        return '';
    }

    private function extractPrice(\SimpleXMLElement $item, array $namespaces): ?float
    {
        foreach (['g', 'bc'] as $ns) {
            if (isset($namespaces[$ns])) {
                $children = $item->children($namespaces[$ns]);
                if (isset($children->price)) {
                    $priceStr = preg_replace('/[^0-9.]/', '', (string) $children->price);
                    return $priceStr !== '' ? (float) $priceStr : null;
                }
            }
        }

        return null;
    }

    private function extractDescription(\SimpleXMLElement $item): string
    {
        $description = strip_tags((string) $item->description);

        return trim($description);
    }

    private function extractAvailability(\SimpleXMLElement $item, array $namespaces): bool
    {
        foreach (['g', 'bc'] as $ns) {
            if (isset($namespaces[$ns])) {
                $children = $item->children($namespaces[$ns]);
                if (isset($children->availability)) {
                    return strtolower((string) $children->availability) === 'in stock';
                }
            }
        }

        return true;
    }
}
