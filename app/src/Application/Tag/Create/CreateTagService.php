<?php

declare(strict_types=1);

namespace App\Application\Tag\Create;

use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\Repository\TagRepository;
use App\Domain\Tag\ValueObject\TagId;
use App\Domain\Tag\ValueObject\TagName;

final class CreateTagService
{
    public function __construct(
        private readonly TagRepository $tagRepository,
    ) {}

    public function execute(CreateTagCommand $command): void
    {
        $tag = $this->buildTag($command);
        $this->save($tag);
    }

    private function buildTag(CreateTagCommand $command): Tag
    {
        return Tag::create(
            id:   TagId::generate(),
            name: TagName::create($command->name),
            slug: $command->slug,
        );
    }

    private function save(Tag $tag): void
    {
        $this->tagRepository->save($tag);
    }
}
