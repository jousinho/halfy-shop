<?php

declare(strict_types=1);

namespace App\Domain\Artwork\ValueObject;

final class ArtworkYear
{
    private function __construct(private readonly int $value) {}

    public static function create(int $value): self
    {
        $currentYear = (int) date('Y');

        if ($value < 1900) {
            throw new \InvalidArgumentException('Artwork year cannot be before 1900.');
        }

        if ($value > $currentYear) {
            throw new \InvalidArgumentException(sprintf('Artwork year cannot be in the future (max: %d).', $currentYear));
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }
}
