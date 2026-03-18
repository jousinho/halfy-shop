<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Tag\Update;

use App\Application\Tag\Update\UpdateTagCommand;
use App\Application\Tag\Update\UpdateTagService;
use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\Repository\TagRepository;
use App\Domain\Tag\ValueObject\TagId;
use App\Domain\Tag\ValueObject\TagName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateTagServiceTest extends TestCase
{
    private TagRepository&MockObject $tagRepository;
    private UpdateTagService $service;

    protected function setUp(): void
    {
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->service       = new UpdateTagService($this->tagRepository);
    }

    public function test_execute__should_update_tag_fields(): void
    {
        $tag = $this->buildTag();
        $this->tagRepository->method('findById')->willReturn($tag);
        $this->tagRepository->method('save');

        $this->service->execute(UpdateTagCommand::create($tag->id()->value(), 'nuevo', 'nuevo'));

        $this->assertSame('nuevo', $tag->name()->value());
        $this->assertSame('nuevo', $tag->slug());
    }

    public function test_execute__should_save_after_update(): void
    {
        $tag = $this->buildTag();
        $this->tagRepository->method('findById')->willReturn($tag);

        $this->tagRepository->expects($this->once())->method('save')->with($tag);

        $this->service->execute(UpdateTagCommand::create($tag->id()->value(), 'nombre', 'nombre'));
    }

    public function test_execute__when_tag_not_found__should_throw_exception(): void
    {
        $this->tagRepository->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $this->service->execute(UpdateTagCommand::create(TagId::generate()->value(), 'nombre', 'nombre'));
    }

    public function test_execute__should_not_change_id(): void
    {
        $tag        = $this->buildTag();
        $originalId = $tag->id()->value();

        $this->tagRepository->method('findById')->willReturn($tag);
        $this->tagRepository->method('save');

        $this->service->execute(UpdateTagCommand::create($originalId, 'otro', 'otro'));

        $this->assertSame($originalId, $tag->id()->value());
    }

    private function buildTag(): Tag
    {
        return Tag::create(TagId::generate(), TagName::create('original'), 'original');
    }
}
