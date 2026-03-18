<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Tag\Delete;

use App\Application\Tag\Delete\DeleteTagCommand;
use App\Application\Tag\Delete\DeleteTagService;
use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\Repository\TagRepository;
use App\Domain\Tag\ValueObject\TagId;
use App\Domain\Tag\ValueObject\TagName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class DeleteTagServiceTest extends TestCase
{
    private TagRepository&MockObject $tagRepository;
    private DeleteTagService $service;

    protected function setUp(): void
    {
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->service       = new DeleteTagService($this->tagRepository);
    }

    public function test_execute__should_call_delete_on_repository(): void
    {
        $tag = $this->buildTag();
        $this->tagRepository->method('findById')->willReturn($tag);

        $this->tagRepository
            ->expects($this->once())
            ->method('delete')
            ->with($tag);

        $this->service->execute(DeleteTagCommand::create($tag->id()->value()));
    }

    public function test_execute__when_tag_not_found__should_throw_exception(): void
    {
        $this->tagRepository->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $this->service->execute(DeleteTagCommand::create(TagId::generate()->value()));
    }

    private function buildTag(): Tag
    {
        return Tag::create(TagId::generate(), TagName::create('abstracto'), 'abstracto');
    }
}
