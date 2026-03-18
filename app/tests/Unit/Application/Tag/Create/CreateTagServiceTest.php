<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Tag\Create;

use App\Application\Tag\Create\CreateTagCommand;
use App\Application\Tag\Create\CreateTagService;
use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\Repository\TagRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class CreateTagServiceTest extends TestCase
{
    private TagRepository&MockObject $tagRepository;
    private CreateTagService $service;

    protected function setUp(): void
    {
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->service       = new CreateTagService($this->tagRepository);
    }

    public function test_execute__should_save_tag(): void
    {
        $this->tagRepository->expects($this->once())->method('save');

        $this->service->execute(CreateTagCommand::create('abstracto', 'abstracto'));
    }

    public function test_execute__should_save_tag_with_correct_data(): void
    {
        $capturedTag = null;
        $this->tagRepository
            ->method('save')
            ->willReturnCallback(function (Tag $tag) use (&$capturedTag): void {
                $capturedTag = $tag;
            });

        $this->service->execute(CreateTagCommand::create('minimalismo', 'minimalismo'));

        $this->assertSame('minimalismo', $capturedTag->name()->value());
        $this->assertSame('minimalismo', $capturedTag->slug());
    }

    public function test_execute__should_generate_a_new_id_each_time(): void
    {
        $ids = [];
        $this->tagRepository
            ->method('save')
            ->willReturnCallback(function (Tag $tag) use (&$ids): void {
                $ids[] = $tag->id()->value();
            });

        $this->service->execute(CreateTagCommand::create('rojo', 'rojo'));
        $this->service->execute(CreateTagCommand::create('azul', 'azul'));

        $this->assertNotSame($ids[0], $ids[1]);
    }

    public function test_execute__when_name_is_empty__should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->execute(CreateTagCommand::create('', 'slug'));
    }
}
