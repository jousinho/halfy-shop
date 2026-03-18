<?php

declare(strict_types=1);

namespace App\Application\Blog\Delete;

use App\Domain\Blog\Entity\Post;
use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Blog\ValueObject\PostId;

final class DeletePostService
{
    public function __construct(
        private readonly PostRepository $postRepository,
    ) {}

    public function execute(DeletePostCommand $command): void
    {
        $post = $this->findPostOrFail($command->id);
        $this->delete($post);
    }

    private function findPostOrFail(string $id): Post
    {
        $post = $this->postRepository->findById(PostId::create($id));

        if ($post === null) {
            throw new \RuntimeException(sprintf('Post "%s" not found.', $id));
        }

        return $post;
    }

    private function delete(Post $post): void
    {
        $this->postRepository->delete($post);
    }
}
