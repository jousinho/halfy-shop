<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\About\Entity;

use App\Domain\About\Entity\AboutPage;
use App\Domain\About\ValueObject\AboutPageId;
use PHPUnit\Framework\TestCase;

final class AboutPageTest extends TestCase
{
    public function test_create_about_page__should_store_all_fields_correctly(): void
    {
        $id   = AboutPageId::generate();
        $page = $this->buildAboutPage($id);

        $this->assertSame($id->value(), $page->id()->value());
        $this->assertSame('<p>Texto biográfico</p>', $page->content());
        $this->assertSame('anna.jpg', $page->photoFilename());
    }

    public function test_create_about_page__when_no_photo__should_return_null(): void
    {
        $page = AboutPage::create(AboutPageId::generate(), 'Contenido', null);

        $this->assertNull($page->photoFilename());
    }

    public function test_create_about_page__should_set_updated_at(): void
    {
        $before = new \DateTimeImmutable();
        $page   = $this->buildAboutPage();
        $after  = new \DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $page->updatedAt());
        $this->assertLessThanOrEqual($after, $page->updatedAt());
    }

    public function test_update_about_page__should_change_content_and_photo(): void
    {
        $page = $this->buildAboutPage();

        $page->update('<p>Nuevo contenido</p>', 'nueva-foto.jpg');

        $this->assertSame('<p>Nuevo contenido</p>', $page->content());
        $this->assertSame('nueva-foto.jpg', $page->photoFilename());
    }

    public function test_update_about_page__should_renew_updated_at(): void
    {
        $page      = $this->buildAboutPage();
        $updatedAt = $page->updatedAt();

        sleep(1);
        $page->update('Nuevo contenido', null);

        $this->assertGreaterThan($updatedAt, $page->updatedAt());
    }

    public function test_update_about_page__can_remove_photo(): void
    {
        $page = $this->buildAboutPage();

        $page->update('Contenido', null);

        $this->assertNull($page->photoFilename());
    }

    public function test_update_about_page__should_not_change_id(): void
    {
        $id   = AboutPageId::generate();
        $page = $this->buildAboutPage($id);

        $page->update('Nuevo contenido', null);

        $this->assertSame($id->value(), $page->id()->value());
    }

    private function buildAboutPage(?AboutPageId $id = null): AboutPage
    {
        return AboutPage::create(
            id:            $id ?? AboutPageId::generate(),
            content:       '<p>Texto biográfico</p>',
            photoFilename: 'anna.jpg',
        );
    }
}
