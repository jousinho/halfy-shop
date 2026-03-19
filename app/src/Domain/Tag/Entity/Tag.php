<?php

declare(strict_types=1);

namespace App\Domain\Tag\Entity;

use App\Domain\Tag\ValueObject\TagId;
use App\Domain\Tag\ValueObject\TagName;
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

    public static function create(TagId $id, TagName $name, string $slug): self
    {
        return new self($id->value(), $name->value(), $slug);
    }

    public function update(TagName $name, string $slug): void
    {
        $this->name = $name->value();
        $this->slug = $slug;
    }

    public function id(): TagId
    {
        return TagId::create($this->id);
    }

    public function name(): TagName
    {
        return TagName::create($this->name);
    }

    public function slug(): string
    {
        return $this->slug;
    }
}
