<?php

declare(strict_types=1);

namespace App\Application\Blog\Update;

use App\Domain\Blog\Entity\Post;
use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Blog\ValueObject\PostId;
use App\Domain\Blog\ValueObject\PostSlug;
use App\Domain\Blog\ValueObject\PostTitle;

final class UpdatePostService
{
    public function __construct(
        private readonly PostRepository $postRepository,
    ) {}

    public function execute(UpdatePostCommand $command): void
    {
        $post = $this->findPostOrFail($command->id);
        $this->updatePostData($post, $command);
        $this->save($post);
    }

    private function findPostOrFail(string $id): Post
    {
        $post = $this->postRepository->findById(PostId::create($id));

        if ($post === null) {
            throw new \RuntimeException(sprintf('Post "%s" not found.', $id));
        }

        return $post;
    }

    private function updatePostData(Post $post, UpdatePostCommand $command): void
    {
        $post->update(
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
