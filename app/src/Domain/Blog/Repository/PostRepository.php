<?php

declare(strict_types=1);

namespace App\Domain\Blog\Repository;

use App\Domain\Blog\Entity\Post;
use App\Domain\Blog\ValueObject\PostId;
use App\Domain\Blog\ValueObject\PostSlug;

interface PostRepository
{
    public function save(Post $post): void;

    public function delete(Post $post): void;

    public function findById(PostId $id): ?Post;

    public function findBySlug(PostSlug $slug): ?Post;

    /** @return Post[] */
    public function findPublished(): array;

    /** @return Post[] */
    public function findAll(): array;
}
