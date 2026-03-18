<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Artwork\ValueObject;

use App\Domain\Artwork\ValueObject\ArtworkYear;
use PHPUnit\Framework\TestCase;

final class ArtworkYearTest extends TestCase
{
    public function test_create_year__when_valid__should_return_instance(): void
    {
        $year = ArtworkYear::create(2012);

        $this->assertSame(2012, $year->value());
    }

    public function test_create_year__when_before_1900__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ArtworkYear::create(1899);
    }

    public function test_create_year__when_in_the_future__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ArtworkYear::create((int) date('Y') + 1);
    }

    public function test_create_year__when_current_year__should_return_instance(): void
    {
        $year = ArtworkYear::create((int) date('Y'));

        $this->assertSame((int) date('Y'), $year->value());
    }
}
