<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateAnnouncement;

final class UpdateAnnouncementCommand
{
    public function __construct(
        public readonly bool $enabled,
        public readonly string $text,
    ) {}
}
