<?php

declare(strict_types=1);

namespace App\Application\Blog\Create;

use App\Domain\Blog\Entity\Post;
use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Blog\ValueObject\PostId;
use App\Domain\Blog\ValueObject\PostSlug;
use App\Domain\Blog\ValueObject\PostTitle;

final class CreatePostService
{
    public function __construct(
        private readonly PostRepository $postRepository,
    ) {}

    public function execute(CreatePostCommand $command): void
    {
        $post = $this->buildPost($command);
        $this->save($post);
    }

    private function buildPost(CreatePostCommand $command): Post
    {
        return Post::create(
            id:          PostId::generate(),
            title:       PostTitle::create($command->title),
            slug:        PostSlug::create($command->slug),
            content:     $command->content,
            publishedAt: $command->publishedAt,
        );
    }

    private function save(Post $post): void
    {
        $this->postRepository->save($post);
    }
}
