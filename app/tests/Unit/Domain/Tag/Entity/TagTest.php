<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Tag\Entity;

use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\ValueObject\TagId;
use App\Domain\Tag\ValueObject\TagName;
use PHPUnit\Framework\TestCase;

final class TagTest extends TestCase
{
    public function test_create_tag__should_store_all_fields_correctly(): void
    {
        $id  = TagId::generate();
        $tag = $this->buildTag($id);

        $this->assertSame($id->value(), $tag->id()->value());
        $this->assertSame('acuarela', $tag->name()->value());
        $this->assertSame('acuarela', $tag->slug());
    }

    public function test_update_tag__should_change_name_and_slug(): void
    {
        $tag = $this->buildTag();

        $tag->update(TagName::create('naturaleza'), 'naturaleza');

        $this->assertSame('naturaleza', $tag->name()->value());
        $this->assertSame('naturaleza', $tag->slug());
    }

    public function test_update_tag__should_not_change_id(): void
    {
        $id  = TagId::generate();
        $tag = $this->buildTag($id);

        $tag->update(TagName::create('naturaleza'), 'naturaleza');

        $this->assertSame($id->value(), $tag->id()->value());
    }

    public function test_create_tag__name_is_stored_lowercase(): void
    {
        $tag = Tag::create(TagId::generate(), TagName::create('GOUACHE'), 'gouache');

        $this->assertSame('gouache', $tag->name()->value());
    }

    private function buildTag(?TagId $id = null): Tag
    {
        return Tag::create(
            id:   $id ?? TagId::generate(),
            name: TagName::create('acuarela'),
            slug: 'acuarela',
        );
    }
}
