<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Blog\Create;

use App\Application\Blog\Create\CreatePostCommand;
use App\Application\Blog\Create\CreatePostService;
use App\Domain\Blog\Entity\Post;
use App\Domain\Blog\Repository\PostRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreatePostServiceTest extends TestCase
{
    private PostRepository&MockObject $postRepository;
    private CreatePostService $service;

    protected function setUp(): void
    {
        $this->postRepository = $this->createMock(PostRepository::class);
        $this->service        = new CreatePostService($this->postRepository);
    }

    public function test_execute__should_save_post(): void
    {
        $this->postRepository->expects($this->once())->method('save');

        $this->service->execute($this->buildCommand());
    }

    public function test_execute__should_save_post_with_correct_data(): void
    {
        $capturedPost = null;
        $this->postRepository
            ->method('save')
            ->willReturnCallback(function (Post $post) use (&$capturedPost): void {
                $capturedPost = $post;
            });

        $this->service->execute(CreatePostCommand::create(
            title:       'Mi primer post',
            slug:        'mi-primer-post',
            content:     '<p>Contenido del post</p>',
            publishedAt: null,
        ));

        $this->assertSame('Mi primer post', $capturedPost->title()->value());
        $this->assertSame('mi-primer-post', $capturedPost->slug()->value());
        $this->assertSame('<p>Contenido del post</p>', $capturedPost->content());
        $this->assertNull($capturedPost->publishedAt());
    }

    public function test_execute__when_published_at_set__should_create_published_post(): void
    {
        $publishedAt  = new \DateTimeImmutable('-1 day');
        $capturedPost = null;

        $this->postRepository
            ->method('save')
            ->willReturnCallback(function (Post $post) use (&$capturedPost): void {
                $capturedPost = $post;
            });

        $this->service->execute(CreatePostCommand::create(
            title:       'Post publicado',
            slug:        'post-publicado',
            content:     'Contenido',
            publishedAt: $publishedAt,
        ));

        $this->assertTrue($capturedPost->isPublished());
    }

    public function test_execute__when_published_at_null__should_create_draft(): void
    {
        $capturedPost = null;
        $this->postRepository
            ->method('save')
            ->willReturnCallback(function (Post $post) use (&$capturedPost): void {
                $capturedPost = $post;
            });

        $this->service->execute(CreatePostCommand::create('Borrador', 'borrador', 'Texto', null));

        $this->assertFalse($capturedPost->isPublished());
    }

    public function test_execute__should_generate_a_new_id_each_time(): void
    {
        $ids = [];
        $this->postRepository
            ->method('save')
            ->willReturnCallback(function (Post $post) use (&$ids): void {
                $ids[] = $post->id()->value();
            });

        $this->service->execute(CreatePostCommand::create('Post A', 'post-a', 'texto', null));
        $this->service->execute(CreatePostCommand::create('Post B', 'post-b', 'texto', null));

        $this->assertNotSame($ids[0], $ids[1]);
    }

    public function test_execute__when_title_is_empty__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->execute(CreatePostCommand::create('', 'slug', 'contenido', null));
    }

    private function buildCommand(): CreatePostCommand
    {
        return CreatePostCommand::create('Título', 'titulo', 'Contenido', null);
    }
}
