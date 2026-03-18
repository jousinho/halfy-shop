<?php

declare(strict_types=1);

namespace App\Domain\Blog\Entity;

use App\Domain\Blog\ValueObject\PostId;
use App\Domain\Blog\ValueObject\PostSlug;
use App\Domain\Blog\ValueObject\PostTitle;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'posts')]
final class Post
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $slug;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $publishedAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    private function __construct(
        string $id,
        string $title,
        string $slug,
        string $content,
        ?\DateTimeImmutable $publishedAt,
    ) {
        $this->id          = $id;
        $this->title       = $title;
        $this->slug        = $slug;
        $this->content     = $content;
        $this->publishedAt = $publishedAt;
        $this->createdAt   = new \DateTimeImmutable();
    }

    public static function create(
        PostId $id,
        PostTitle $title,
        PostSlug $slug,
        string $content,
        ?\DateTimeImmutable $publishedAt,
    ): self {
        return new self($id->value(), $title->value(), $slug->value(), $content, $publishedAt);
    }

    public function update(
        PostTitle $title,
        PostSlug $slug,
        string $content,
        ?\DateTimeImmutable $publishedAt,
    ): void {
        $this->title       = $title->value();
        $this->slug        = $slug->value();
        $this->content     = $content;
        $this->publishedAt = $publishedAt;
    }

    public function isPublished(): bool
    {
        return $this->publishedAt !== null && $this->publishedAt <= new \DateTimeImmutable();
    }

    public function id(): PostId
    {
        return PostId::create($this->id);
    }

    public function title(): PostTitle
    {
        return PostTitle::create($this->title);
    }

    public function slug(): PostSlug
    {
        return PostSlug::create($this->slug);
    }

    public function content(): string
    {
        return $this->content;
    }

    public function publishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
