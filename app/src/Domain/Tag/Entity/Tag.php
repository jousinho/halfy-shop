<?php

declare(strict_types=1);

namespace App\Domain\Tag\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tags')]
final class Tag
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 50)]
    private string $name;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $slug;

    private function __construct(string $id, string $name, string $slug)
    {
        $this->id   = $id;
        $this->name = $name;
        $this->slug = $slug;
    }

    public static function create(
        \App\Domain\Tag\ValueObject\TagId $id,
        \App\Domain\Tag\ValueObject\TagName $name,
        string $slug,
    ): self {
        return new self($id->value(), $name->value(), $slug);
    }

    public function id(): \App\Domain\Tag\ValueObject\TagId
    {
        return \App\Domain\Tag\ValueObject\TagId::create($this->id);
    }

    public function name(): \App\Domain\Tag\ValueObject\TagName
    {
        return \App\Domain\Tag\ValueObject\TagName::create($this->name);
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function update(\App\Domain\Tag\ValueObject\TagName $name, string $slug): void
    {
        $this->name = $name->value();
        $this->slug = $slug;
    }
}
