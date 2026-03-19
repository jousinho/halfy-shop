<?php

declare(strict_types=1);

namespace App\Application\Sync;

use App\Application\Shared\BigCartelFeedFetcher;
use App\Application\Shared\RemoteImageDownloader;
use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Repository\ArtworkRepository;
use App\Domain\Artwork\ValueObject\ArtworkId;
use App\Domain\Artwork\ValueObject\ArtworkTitle;
use App\Domain\Artwork\ValueObject\ArtworkYear;
use App\Domain\Artwork\ValueObject\Dimensions;
use App\Domain\Artwork\ValueObject\Price;
use App\Domain\Artwork\ValueObject\Technique;
use App\Domain\Sync\Entity\SyncLog;
use App\Domain\Sync\Repository\SyncLogRepository;
use Ramsey\Uuid\Uuid;

final class SyncWithBigCartelService
{
    public function __construct(
        private readonly BigCartelFeedFetcher $feedFetcher,
        private readonly ArtworkRepository $artworkRepository,
        private readonly SyncLogRepository $syncLogRepository,
        private readonly RemoteImageDownloader $imageDownloader,
        private readonly string $feedUrl,
    ) {}

    public function execute(): SyncLog
    {
        $items   = $this->fetchItemsFromFeed();
        $results = $this->processItems($items);
        $log     = $this->buildSyncLog($results);
        $this->saveSyncLog($log);

        return $log;
    }

    private function fetchItemsFromFeed(): array
    {
        return $this->feedFetcher->fetch($this->feedUrl);
    }

    private function processItems(array $items): array
    {
        $created   = 0;
        $updated   = 0;
        $unchanged = 0;
        $logLines  = [];

        foreach ($items as $item) {
            $result     = $this->processItem($item);
            $logLines[] = ucfirst($result) . ': ' . $item['title'];

            match ($result) {
                'created'   => $created++,
                'updated'   => $updated++,
                'unchanged' => $unchanged++,
            };
        }

        return [
            'created'   => $created,
            'updated'   => $updated,
            'unchanged' => $unchanged,
            'log'       => implode("\n", $logLines),
        ];
    }

    private function processItem(array $item): string
    {
        $existing = $this->artworkRepository->findByShopUrl($item['shopUrl']);

        if ($existing === null) {
            $this->createArtworkFromItem($item);

            return 'created';
        }

        if ($this->hasChanged($existing, $item)) {
            $this->updateArtworkFromItem($existing, $item);

            return 'updated';
        }

        return 'unchanged';
    }

    private function hasChanged(Artwork $artwork, array $item): bool
    {
        return $artwork->title()->value() !== $item['title']
            || $artwork->isAvailable() !== $item['isAvailable']
            || $artwork->price()?->value() !== $item['price'];
    }

    private function createArtworkFromItem(array $item): void
    {
        $imageFilename = $this->imageDownloader->download($item['imageUrl'], 'artworks');

        $artwork = Artwork::create(
            id:            ArtworkId::generate(),
            title:         ArtworkTitle::create($item['title']),
            description:   $item['description'] !== '' ? $item['description'] : null,
            technique:     Technique::create('—'),
            dimensions:    Dimensions::create('—'),
            year:          ArtworkYear::create((int) date('Y')),
            price:         $item['price'] !== null ? Price::create($item['price']) : null,
            imageFilename: $imageFilename,
            shopUrl:       $item['shopUrl'],
            isAvailable:   $item['isAvailable'],
            sortOrder:     $this->artworkRepository->findNextSortOrder(),
        );

        $this->artworkRepository->save($artwork);
    }

    private function updateArtworkFromItem(Artwork $artwork, array $item): void
    {
        $artwork->update(
            title:       ArtworkTitle::create($item['title']),
            description: $item['description'] !== '' ? $item['description'] : null,
            technique:   $artwork->technique(),
            dimensions:  $artwork->dimensions(),
            year:        $artwork->year(),
            price:       $item['price'] !== null ? Price::create($item['price']) : null,
            shopUrl:     $item['shopUrl'],
            isAvailable: $item['isAvailable'],
        );

        $this->artworkRepository->save($artwork);
    }

    private function buildSyncLog(array $results): SyncLog
    {
        return SyncLog::create(
            id:        Uuid::uuid4()->toString(),
            created:   $results['created'],
            updated:   $results['updated'],
            unchanged: $results['unchanged'],
            log:       $results['log'],
        );
    }

    private function saveSyncLog(SyncLog $log): void
    {
        $this->syncLogRepository->save($log);
    }
}
