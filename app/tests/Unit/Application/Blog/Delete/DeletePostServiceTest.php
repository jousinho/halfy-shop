<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Blog\Delete;

use App\Application\Blog\Delete\DeletePostCommand;
use App\Application\Blog\Delete\DeletePostService;
use App\Domain\Blog\Entity\Post;
use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Blog\ValueObject\PostId;
use App\Domain\Blog\ValueObject\PostSlug;
use App\Domain\Blog\ValueObject\PostTitle;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeletePostServiceTest extends TestCase
{
    private PostRepository&MockObject $postRepository;
    private DeletePostService $service;

    protected function setUp(): void
    {
        $this->postRepository = $this->createMock(PostRepository::class);
        $this->service        = new DeletePostService($this->postRepository);
    }

    public function test_execute__should_call_delete_on_repository(): void
    {
        $post = $this->buildPost();
        $this->postRepository->method('findById')->willReturn($post);

        $this->postRepository
            ->expects($this->once())
            ->method('delete')
            ->with($post);

        $this->service->execute(DeletePostCommand::create($post->id()->value()));
    }

    public function test_execute__when_post_not_found__should_throw_exception(): void
    {
        $this->postRepository->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $this->service->execute(DeletePostCommand::create(PostId::generate()->value()));
    }

    private function buildPost(): Post
    {
        return Post::create(
            id:          PostId::generate(),
            title:       PostTitle::create('Mi post'),
            slug:        PostSlug::create('mi-post'),
            content:     'Contenido',
            publishedAt: null,
        );
    }
}
