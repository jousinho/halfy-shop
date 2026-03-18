<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Artwork\Entity;

use App\Domain\Artwork\Entity\Artwork;
use App\Domain\Artwork\Event\ArtworkCreated;
use App\Domain\Artwork\Event\ArtworkDeleted;
use App\Domain\Artwork\Event\ArtworkUpdated;
use App\Domain\Artwork\ValueObject\ArtworkId;
use App\Domain\Artwork\ValueObject\ArtworkTitle;
use App\Domain\Artwork\ValueObject\ArtworkYear;
use App\Domain\Artwork\ValueObject\Dimensions;
use App\Domain\Artwork\ValueObject\Price;
use App\Domain\Artwork\ValueObject\Technique;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Category\ValueObject\CategoryName;
use App\Domain\Category\ValueObject\CategorySlug;
use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\ValueObject\TagId;
use App\Domain\Tag\ValueObject\TagName;
use PHPUnit\Framework\TestCase;

final class ArtworkTest extends TestCase
{
    // --- Domain Events ---

    public function test_create_artwork__should_dispatch_artwork_created_event(): void
    {
        $artwork = $this->buildArtwork();
        $events  = $artwork->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArtworkCreated::class, $events[0]);
    }

    public function test_create_artwork__event_should_contain_artwork_id(): void
    {
        $id      = ArtworkId::generate();
        $artwork = $this->buildArtwork($id);
        $events  = $artwork->pullDomainEvents();

        $this->assertSame($id->value(), $events[0]->aggregateId());
    }

    public function test_update_artwork__should_dispatch_artwork_updated_event(): void
    {
        $artwork = $this->buildArtwork();
        $artwork->pullDomainEvents();

        $artwork->update(
            ArtworkTitle::create('Nuevo título'),
            null,
            Technique::create('Acuarela'),
            Dimensions::create('20x30 cm'),
            ArtworkYear::create(2020),
            Price::create(50.00),
            null,
            true,
        );

        $events = $artwork->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArtworkUpdated::class, $events[0]);
    }

    public function test_mark_as_deleted__should_dispatch_artwork_deleted_event(): void
    {
        $artwork = $this->buildArtwork();
        $artwork->pullDomainEvents();

        $artwork->markAsDeleted();

        $events = $artwork->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArtworkDeleted::class, $events[0]);
    }

    public function test_pull_domain_events__should_clear_events_after_pulling(): void
    {
        $artwork = $this->buildArtwork();
        $artwork->pullDomainEvents();

        $this->assertCount(0, $artwork->pullDomainEvents());
    }

    // --- Getters after create ---

    public function test_create_artwork__should_store_all_fields_correctly(): void
    {
        $id      = ArtworkId::generate();
        $artwork = $this->buildArtwork($id);

        $this->assertSame($id->value(), $artwork->id()->value());
        $this->assertSame('Podría volar', $artwork->title()->value());
        $this->assertSame('Una obra de grabado', $artwork->description());
        $this->assertSame('Fotopolímero', $artwork->technique()->value());
        $this->assertSame('35x37 cm', $artwork->dimensions()->value());
        $this->assertSame(2012, $artwork->year()->value());
        $this->assertSame(120.0, $artwork->price()->value());
        $this->assertSame('volar.jpg', $artwork->imageFilename());
        $this->assertSame('https://annapownall.bigcartel.com/product/podria-volar', $artwork->shopUrl());
        $this->assertTrue($artwork->isAvailable());
        $this->assertSame(1, $artwork->sortOrder());
    }

    public function test_create_artwork__when_no_price__should_return_null_price(): void
    {
        $artwork = Artwork::create(
            id:            ArtworkId::generate(),
            title:         ArtworkTitle::create('Sin precio'),
            description:   null,
            technique:     Technique::create('Fotopolímero'),
            dimensions:    Dimensions::create('35x37 cm'),
            year:          ArtworkYear::create(2012),
            price:         null,
            imageFilename: 'volar.jpg',
            shopUrl:       null,
            isAvailable:   false,
            sortOrder:     1,
        );

        $this->assertNull($artwork->price());
        $this->assertNull($artwork->description());
        $this->assertNull($artwork->shopUrl());
        $this->assertFalse($artwork->isAvailable());
    }

    public function test_create_artwork__should_initialize_empty_collections(): void
    {
        $artwork = $this->buildArtwork();

        $this->assertCount(0, $artwork->categories());
        $this->assertCount(0, $artwork->tags());
    }

    // --- Update ---

    public function test_update_artwork__should_change_fields(): void
    {
        $artwork = $this->buildArtwork();
        $artwork->pullDomainEvents();

        $artwork->update(
            ArtworkTitle::create('Título actualizado'),
            'Nueva descripción',
            Technique::create('Acuarela'),
            Dimensions::create('20x30 cm'),
            ArtworkYear::create(2020),
            Price::create(200.00),
            'https://nueva-tienda.com/obra',
            false,
        );

        $this->assertSame('Título actualizado', $artwork->title()->value());
        $this->assertSame('Nueva descripción', $artwork->description());
        $this->assertSame('Acuarela', $artwork->technique()->value());
        $this->assertSame('20x30 cm', $artwork->dimensions()->value());
        $this->assertSame(2020, $artwork->year()->value());
        $this->assertSame(200.0, $artwork->price()->value());
        $this->assertSame('https://nueva-tienda.com/obra', $artwork->shopUrl());
        $this->assertFalse($artwork->isAvailable());
    }

    public function test_update_image__should_change_image_filename(): void
    {
        $artwork = $this->buildArtwork();

        $artwork->updateImage('nueva-imagen.jpg');

        $this->assertSame('nueva-imagen.jpg', $artwork->imageFilename());
    }

    // --- Sort order ---

    public function test_set_sort_order__should_update_sort_order(): void
    {
        $artwork = $this->buildArtwork();

        $artwork->setSortOrder(5);

        $this->assertSame(5, $artwork->sortOrder());
    }

    // --- Categories ---

    public function test_assign_category__should_add_to_collection(): void
    {
        $artwork  = $this->buildArtwork();
        $category = $this->buildCategory();

        $artwork->assignCategory($category);

        $this->assertCount(1, $artwork->categories());
        $this->assertTrue($artwork->categories()->contains($category));
    }

    public function test_assign_category__when_already_assigned__should_not_duplicate(): void
    {
        $artwork  = $this->buildArtwork();
        $category = $this->buildCategory();

        $artwork->assignCategory($category);
        $artwork->assignCategory($category);

        $this->assertCount(1, $artwork->categories());
    }

    public function test_remove_category__should_remove_from_collection(): void
    {
        $artwork  = $this->buildArtwork();
        $category = $this->buildCategory();

        $artwork->assignCategory($category);
        $artwork->removeCategory($category);

        $this->assertCount(0, $artwork->categories());
    }

    // --- Tags ---

    public function test_assign_tag__should_add_to_collection(): void
    {
        $artwork = $this->buildArtwork();
        $tag     = $this->buildTag();

        $artwork->assignTag($tag);

        $this->assertCount(1, $artwork->tags());
        $this->assertTrue($artwork->tags()->contains($tag));
    }

    public function test_assign_tag__when_already_assigned__should_not_duplicate(): void
    {
        $artwork = $this->buildArtwork();
        $tag     = $this->buildTag();

        $artwork->assignTag($tag);
        $artwork->assignTag($tag);

        $this->assertCount(1, $artwork->tags());
    }

    public function test_remove_tag__should_remove_from_collection(): void
    {
        $artwork = $this->buildArtwork();
        $tag     = $this->buildTag();

        $artwork->assignTag($tag);
        $artwork->removeTag($tag);

        $this->assertCount(0, $artwork->tags());
    }

    // --- Helpers ---

    private function buildArtwork(?ArtworkId $id = null): Artwork
    {
        return Artwork::create(
            id:            $id ?? ArtworkId::generate(),
            title:         ArtworkTitle::create('Podría volar'),
            description:   'Una obra de grabado',
            technique:     Technique::create('Fotopolímero'),
            dimensions:    Dimensions::create('35x37 cm'),
            year:          ArtworkYear::create(2012),
            price:         Price::create(120.00),
            imageFilename: 'volar.jpg',
            shopUrl:       'https://annapownall.bigcartel.com/product/podria-volar',
            isAvailable:   true,
            sortOrder:     1,
        );
    }

    private function buildCategory(): Category
    {
        return Category::create(
            CategoryId::generate(),
            CategoryName::create('Grabados'),
            CategorySlug::create('grabados'),
            1,
        );
    }

    private function buildTag(): Tag
    {
        return Tag::create(
            TagId::generate(),
            TagName::create('acuarela'),
            'acuarela',
        );
    }
}
