<?php

declare(strict_types=1);

namespace App\Domain\Category\Entity;

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Category\ValueObject\CategoryName;
use App\Domain\Category\ValueObject\CategorySlug;
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
        CategoryId $id,
        CategoryName $name,
        CategorySlug $slug,
        int $sortOrder,
    ): self {
        return new self($id->value(), $name->value(), $slug->value(), $sortOrder);
    }

    public function update(CategoryName $name, CategorySlug $slug, int $sortOrder): void
    {
        $this->name      = $name->value();
        $this->slug      = $slug->value();
        $this->sortOrder = $sortOrder;
    }

    public function id(): CategoryId
    {
        return CategoryId::create($this->id);
    }

    public function name(): CategoryName
    {
        return CategoryName::create($this->name);
    }

    public function slug(): CategorySlug
    {
        return CategorySlug::create($this->slug);
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
}
