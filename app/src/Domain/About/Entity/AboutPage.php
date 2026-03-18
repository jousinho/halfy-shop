<?php

declare(strict_types=1);

namespace App\Domain\About\Entity;

use App\Domain\About\ValueObject\AboutPageId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'about_page')]
final class AboutPage
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photoFilename;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    private function __construct(string $id, string $content, ?string $photoFilename)
    {
        $this->id            = $id;
        $this->content       = $content;
        $this->photoFilename = $photoFilename;
        $this->updatedAt     = new \DateTimeImmutable();
    }

    public static function create(AboutPageId $id, string $content, ?string $photoFilename): self
    {
        return new self($id->value(), $content, $photoFilename);
    }

    public function update(string $content, ?string $photoFilename): void
    {
        $this->content       = $content;
        $this->photoFilename = $photoFilename;
        $this->updatedAt     = new \DateTimeImmutable();
    }

    public function id(): AboutPageId
    {
        return AboutPageId::create($this->id);
    }

    public function content(): string
    {
        return $this->content;
    }

    public function photoFilename(): ?string
    {
        return $this->photoFilename;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
