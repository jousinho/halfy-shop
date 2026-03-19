<?php

declare(strict_types=1);

namespace App\Application\Shared;

interface BigCartelFeedFetcher
{
    /**
     * @return array<array{title: string, description: string, price: ?float, shopUrl: string, imageUrl: string, isAvailable: bool}>
     */
    public function fetch(string $feedUrl): array;
}
