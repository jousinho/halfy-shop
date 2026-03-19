<?php

declare(strict_types=1);

namespace App\Application\Tag\Delete;

use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\Repository\TagRepository;
use App\Domain\Tag\ValueObject\TagId;

final class DeleteTagService
{
    public function __construct(
        private readonly TagRepository $tagRepository,
    ) {}

    public function execute(DeleteTagCommand $command): void
    {
        $tag = $this->findTagOrFail($command->id);
        $this->delete($tag);
    }

    private function findTagOrFail(string $id): Tag
    {
        $tag = $this->tagRepository->findById(TagId::create($id));

        if ($tag === null) {
            throw new \RuntimeException(sprintf('Tag "%s" not found.', $id));
        }

        return $tag;
    }

    private function delete(Tag $tag): void
    {
        $this->tagRepository->delete($tag);
    }
}
