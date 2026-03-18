<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Blog\Update;

use App\Application\Blog\Update\UpdatePostCommand;
use App\Application\Blog\Update\UpdatePostService;
use App\Domain\Blog\Entity\Post;
use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Blog\ValueObject\PostId;
use App\Domain\Blog\ValueObject\PostSlug;
use App\Domain\Blog\ValueObject\PostTitle;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdatePostServiceTest extends TestCase
{
    private PostRepository&MockObject $postRepository;
    private UpdatePostService $service;

    protected function setUp(): void
    {
        $this->postRepository = $this->createMock(PostRepository::class);
        $this->service        = new UpdatePostService($this->postRepository);
    }

    public function test_execute__should_update_post_fields(): void
    {
        $post = $this->buildPost();
        $this->postRepository->method('findById')->willReturn($post);
        $this->postRepository->method('save');

        $this->service->execute(UpdatePostCommand::create(
            id:          $post->id()->value(),
            title:       'Título actualizado',
            slug:        'titulo-actualizado',
            content:     '<p>Contenido nuevo</p>',
            publishedAt: null,
        ));

        $this->assertSame('Título actualizado', $post->title()->value());
        $this->assertSame('titulo-actualizado', $post->slug()->value());
        $this->assertSame('<p>Contenido nuevo</p>', $post->content());
    }

    public function test_execute__should_save_after_update(): void
    {
        $post = $this->buildPost();
        $this->postRepository->method('findById')->willReturn($post);

        $this->postRepository->expects($this->once())->method('save')->with($post);

        $this->service->execute(UpdatePostCommand::create(
            $post->id()->value(), 'Título', 'titulo', 'Contenido', null,
        ));
    }

    public function test_execute__when_post_not_found__should_throw_exception(): void
    {
        $this->postRepository->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $this->service->execute(UpdatePostCommand::create(
            PostId::generate()->value(), 'Título', 'titulo', 'Contenido', null,
        ));
    }

    public function test_execute__when_published_at_set__should_mark_as_published(): void
    {
        $post        = $this->buildPost();
        $publishedAt = new \DateTimeImmutable('-1 hour');

        $this->postRepository->method('findById')->willReturn($post);
        $this->postRepository->method('save');

        $this->service->execute(UpdatePostCommand::create(
            $post->id()->value(), 'Título', 'titulo', 'Contenido', $publishedAt,
        ));

        $this->assertTrue($post->isPublished());
    }

    public function test_execute__when_published_at_cleared__should_unpublish(): void
    {
        $post = $this->buildPost(publishedAt: new \DateTimeImmutable('-1 day'));

        $this->postRepository->method('findById')->willReturn($post);
        $this->postRepository->method('save');

        $this->service->execute(UpdatePostCommand::create(
            $post->id()->value(), 'Título', 'titulo', 'Contenido', null,
        ));

        $this->assertFalse($post->isPublished());
    }

    public function test_execute__should_not_change_id(): void
    {
        $post       = $this->buildPost();
        $originalId = $post->id()->value();

        $this->postRepository->method('findById')->willReturn($post);
        $this->postRepository->method('save');

        $this->service->execute(UpdatePostCommand::create(
            $originalId, 'Otro título', 'otro-titulo', 'Contenido', null,
        ));

        $this->assertSame($originalId, $post->id()->value());
    }

    private function buildPost(?\DateTimeImmutable $publishedAt = null): Post
    {
        return Post::create(
            id:          PostId::generate(),
            title:       PostTitle::create('Título original'),
            slug:        PostSlug::create('titulo-original'),
            content:     'Contenido original',
            publishedAt: $publishedAt,
        );
    }
}
