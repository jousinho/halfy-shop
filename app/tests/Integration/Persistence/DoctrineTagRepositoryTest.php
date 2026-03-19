<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence;

use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\Repository\TagRepository;
use App\Domain\Tag\ValueObject\TagId;
use App\Domain\Tag\ValueObject\TagName;
use App\Tests\Integration\IntegrationTestCase;

final class DoctrineTagRepositoryTest extends IntegrationTestCase
{
    private TagRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getService(TagRepository::class);
    }

    public function test_save_and_findById__should_persist_and_retrieve(): void
    {
        $tag = Tag::create(TagId::generate(), TagName::create('abstracto'), 'abstracto');
        $this->repository->save($tag);

        $found = $this->repository->findById($tag->id());

        $this->assertNotNull($found);
        $this->assertSame('abstracto', $found->name()->value());
    }

    public function test_findById__when_not_exists__should_return_null(): void
    {
        $this->assertNull($this->repository->findById(TagId::generate()));
    }

    public function test_findBySlug__should_return_matching_tag(): void
    {
        $tag = Tag::create(TagId::generate(), TagName::create('figurativo'), 'figurativo');
        $this->repository->save($tag);

        $found = $this->repository->findBySlug('figurativo');

        $this->assertNotNull($found);
        $this->assertSame($tag->id()->value(), $found->id()->value());
    }

    public function test_findBySlug__when_not_exists__should_return_null(): void
    {
        $this->assertNull($this->repository->findBySlug('slug-inexistente'));
    }

    public function test_findAll__should_return_sorted_by_name(): void
    {
        $this->repository->save(Tag::create(TagId::generate(), TagName::create('zzz-tag'), 'zzz-tag'));
        $this->repository->save(Tag::create(TagId::generate(), TagName::create('aaa-tag'), 'aaa-tag'));

        $all   = $this->repository->findAll();
        $names = array_map(fn ($t) => $t->name()->value(), $all);

        $sorted = $names;
        sort($sorted);
        $this->assertSame($sorted, $names);
    }

    public function test_delete__should_remove_tag(): void
    {
        $tag = Tag::create(TagId::generate(), TagName::create('borrar'), 'borrar');
        $this->repository->save($tag);
        $this->repository->delete($tag);

        $this->assertNull($this->repository->findById($tag->id()));
    }
}
