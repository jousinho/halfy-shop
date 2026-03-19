<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence;

use App\Domain\Blog\Entity\Post;
use App\Domain\Blog\Repository\PostRepository;
use App\Domain\Blog\ValueObject\PostId;
use App\Domain\Blog\ValueObject\PostSlug;
use App\Domain\Blog\ValueObject\PostTitle;
use App\Tests\Integration\IntegrationTestCase;

final class DoctrinePostRepositoryTest extends IntegrationTestCase
{
    private PostRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getService(PostRepository::class);
    }

    public function test_save_and_findById__should_persist_and_retrieve(): void
    {
        $post = $this->buildPost();
        $this->repository->save($post);

        $found = $this->repository->findById($post->id());

        $this->assertNotNull($found);
        $this->assertSame('Mi Entrada', $found->title()->value());
    }

    public function test_findBySlug__should_return_matching_post(): void
    {
        $post = $this->buildPost(slug: 'mi-entrada-unica');
        $this->repository->save($post);

        $found = $this->repository->findBySlug(PostSlug::create('mi-entrada-unica'));

        $this->assertNotNull($found);
        $this->assertSame($post->id()->value(), $found->id()->value());
    }

    public function test_findBySlug__when_not_exists__should_return_null(): void
    {
        $this->assertNull($this->repository->findBySlug(PostSlug::create('no-existe')));
    }

    public function test_findPublished__should_return_only_published_posts(): void
    {
        $published = $this->buildPost(publishedAt: new \DateTimeImmutable('-1 day'));
        $draft     = $this->buildPost(slug: 'borrador', publishedAt: null);
        $future    = $this->buildPost(slug: 'futuro', publishedAt: new \DateTimeImmutable('+1 day'));

        $this->repository->save($published);
        $this->repository->save($draft);
        $this->repository->save($future);

        $results = $this->repository->findPublished();
        $ids     = array_map(fn ($p) => $p->id()->value(), $results);

        $this->assertContains($published->id()->value(), $ids);
        $this->assertNotContains($draft->id()->value(), $ids);
        $this->assertNotContains($future->id()->value(), $ids);
    }

    public function test_findPublished__should_be_sorted_desc_by_date(): void
    {
        $old   = $this->buildPost(slug: 'old', publishedAt: new \DateTimeImmutable('-10 days'));
        $new   = $this->buildPost(slug: 'new', publishedAt: new \DateTimeImmutable('-1 day'));

        $this->repository->save($old);
        $this->repository->save($new);

        $results = $this->repository->findPublished();
        $dates   = array_map(fn ($p) => $p->publishedAt()->getTimestamp(), $results);

        for ($i = 1; $i < count($dates); $i++) {
            $this->assertGreaterThanOrEqual($dates[$i], $dates[$i - 1]);
        }
    }

    public function test_delete__should_remove_post(): void
    {
        $post = $this->buildPost();
        $this->repository->save($post);
        $this->repository->delete($post);

        $this->assertNull($this->repository->findById($post->id()));
    }

    private function buildPost(
        string $slug = 'mi-entrada',
        ?\DateTimeImmutable $publishedAt = null,
    ): Post {
        return Post::create(
            id:          PostId::generate(),
            title:       PostTitle::create('Mi Entrada'),
            slug:        PostSlug::create($slug),
            content:     '<p>Contenido de prueba.</p>',
            publishedAt: $publishedAt,
        );
    }
}
