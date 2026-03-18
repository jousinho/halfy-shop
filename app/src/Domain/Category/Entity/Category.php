<?php

declare(strict_types=1);

namespace App\Domain\Category\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'categories')]
final class Category
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $slug;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder;

    private function __construct(string $id, string $name, string $slug, int $sortOrder)
    {
        $this->id        = $id;
        $this->name      = $name;
        $this->slug      = $slug;
        $this->sortOrder = $sortOrder;
    }

    public static function create(
        \App\Domain\Category\ValueObject\CategoryId $id,
        \App\Domain\Category\ValueObject\CategoryName $name,
        \App\Domain\Category\ValueObject\CategorySlug $slug,
        int $sortOrder,
    ): self {
        return new self($id->value(), $name->value(), $slug->value(), $sortOrder);
    }

    public function id(): \App\Domain\Category\ValueObject\CategoryId
    {
        return \App\Domain\Category\ValueObject\CategoryId::create($this->id);
    }

    public function name(): \App\Domain\Category\ValueObject\CategoryName
    {
        return \App\Domain\Category\ValueObject\CategoryName::create($this->name);
    }

    public function slug(): \App\Domain\Category\ValueObject\CategorySlug
    {
        return \App\Domain\Category\ValueObject\CategorySlug::create($this->slug);
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function update(
        \App\Domain\Category\ValueObject\CategoryName $name,
        \App\Domain\Category\ValueObject\CategorySlug $slug,
        int $sortOrder,
    ): void {
        $this->name      = $name->value();
        $this->slug      = $slug->value();
        $this->sortOrder = $sortOrder;
    }
}
