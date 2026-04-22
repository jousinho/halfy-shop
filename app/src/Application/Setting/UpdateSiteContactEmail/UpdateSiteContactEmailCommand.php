<?php

declare(strict_types=1);

namespace App\Application\Setting\UpdateSiteContactEmail;

final class UpdateSiteContactEmailCommand
{
    public function __construct(public readonly string $email) {}
}
