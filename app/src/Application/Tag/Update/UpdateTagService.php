<?php

declare(strict_types=1);

namespace App\Application\Tag\Update;

use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\Repository\TagRepository;
use App\Domain\Tag\ValueObject\TagId;
use App\Domain\Tag\ValueObject\TagName;

final class UpdateTagService
{
    public function __construct(
        private readonly TagRepository $tagRepository,
    ) {}

    public function execute(UpdateTagCommand $command): void
    {
        $tag = $this->findTagOrFail($command->id);
        $this->updateTagData($tag, $command);
        $this->save($tag);
    }

    private function findTagOrFail(string $id): Tag
    {
        $tag = $this->tagRepository->findById(TagId::create($id));

        if ($tag === null) {
            throw new \RuntimeException(sprintf('Tag "%s" not found.', $id));
        }

        return $tag;
    }

    private function updateTagData(Tag $tag, UpdateTagCommand $command): void
    {
        $tag->update(
            name: TagName::create($command->name),
            slug: $command->slug,
        );
    }

    private function save(Tag $tag): void
    {
        $this->tagRepository->save($tag);
    }
}
