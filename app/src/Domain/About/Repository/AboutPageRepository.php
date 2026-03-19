<?php

declare(strict_types=1);

namespace App\Domain\About\Repository;

use App\Domain\About\Entity\AboutPage;

interface AboutPageRepository
{
    public function save(AboutPage $page): void;

    public function findPage(): ?AboutPage;
}
