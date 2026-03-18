<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Blog\Entity;

use App\Domain\Blog\Entity\Post;
use App\Domain\Blog\ValueObject\PostId;
use App\Domain\Blog\ValueObject\PostSlug;
use App\Domain\Blog\ValueObject\PostTitle;
use PHPUnit\Framework\TestCase;

final class PostTest extends TestCase
{
    public function test_create_post__should_store_all_fields_correctly(): void
    {
        $id          = PostId::generate();
        $publishedAt = new \DateTimeImmutable('2026-01-15');
        $post        = $this->buildPost($id, $publishedAt);

        $this->assertSame($id->value(), $post->id()->value());
        $this->assertSame('Mi primer post', $post->title()->value());
        $this->assertSame('mi-primer-post', $post->slug()->value());
        $this->assertSame('Contenido del post', $post->content());
        $this->assertSame($publishedAt, $post->publishedAt());
    }

    public function test_create_post__when_no_published_at__should_be_draft(): void
    {
        $post = $this->buildPost(publishedAt: null);

        $this->assertNull($post->publishedAt());
        $this->assertFalse($post->isPublished());
    }

    public function test_is_published__when_published_at_is_in_the_past__should_return_true(): void
    {
        $post = $this->buildPost(publishedAt: new \DateTimeImmutable('2000-01-01'));

        $this->assertTrue($post->isPublished());
    }

    public function test_is_published__when_published_at_is_in_the_future__should_return_false(): void
    {
        $post = $this->buildPost(publishedAt: new \DateTimeImmutable('2099-01-01'));

        $this->assertFalse($post->isPublished());
    }

    public function test_update_post__should_change_fields(): void
    {
        $post        = $this->buildPost();
        $publishedAt = new \DateTimeImmutable('2026-03-01');

        $post->update(
            PostTitle::create('Título actualizado'),
            PostSlug::create('titulo-actualizado'),
            'Nuevo contenido',
            $publishedAt,
        );

        $this->assertSame('Título actualizado', $post->title()->value());
        $this->assertSame('titulo-actualizado', $post->slug()->value());
        $this->assertSame('Nuevo contenido', $post->content());
        $this->assertSame($publishedAt, $post->publishedAt());
    }

    public function test_update_post__should_not_change_id(): void
    {
        $id   = PostId::generate();
        $post = $this->buildPost($id);

        $post->update(
            PostTitle::create('Título actualizado'),
            PostSlug::create('titulo-actualizado'),
            'Nuevo contenido',
            null,
        );

        $this->assertSame($id->value(), $post->id()->value());
    }

    public function test_update_post__can_unpublish_by_setting_null(): void
    {
        $post = $this->buildPost(publishedAt: new \DateTimeImmutable('2026-01-01'));

        $post->update(
            PostTitle::create('Mi primer post'),
            PostSlug::create('mi-primer-post'),
            'Contenido del post',
            null,
        );

        $this->assertNull($post->publishedAt());
        $this->assertFalse($post->isPublished());
    }

    private function buildPost(?PostId $id = null, ?\DateTimeImmutable $publishedAt = new \DateTimeImmutable('2026-01-15')): Post
    {
        return Post::create(
            id:          $id ?? PostId::generate(),
            title:       PostTitle::create('Mi primer post'),
            slug:        PostSlug::create('mi-primer-post'),
            content:     'Contenido del post',
            publishedAt: $publishedAt,
        );
    }
}
