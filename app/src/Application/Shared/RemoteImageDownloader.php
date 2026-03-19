<?php

declare(strict_types=1);

namespace App\Application\Shared;

interface RemoteImageDownloader
{
    public function download(string $url, string $destinationDir): string;
}
