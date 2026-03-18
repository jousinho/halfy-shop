<?php

declare(strict_types=1);

namespace App\Domain\Artwork\Entity;

use App\Domain\Artwork\Event\ArtworkCreated;
use App\Domain\Artwork\Event\ArtworkDeleted;
use App\Domain\Artwork\Event\ArtworkUpdated;
use App\Domain\Artwork\ValueObject\ArtworkId;
use App\Domain\Artwork\ValueObject\ArtworkTitle;
use App\Domain\Artwork\ValueObject\ArtworkYear;
use App\Domain\Artwork\ValueObject\Dimensions;
use App\Domain\Artwork\ValueObject\Price;
use App\Domain\Artwork\ValueObject\Technique;
use App\Domain\Category\Entity\Category;
use App\Domain\Tag\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'artworks')]
final class Artwork
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'string', length: 100)]
    private string $technique;

    #[ORM\Column(type: 'string', length: 50)]
    private string $dimensions;

    #[ORM\Column(type: 'integer')]
    private int $year;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $price;

    #[ORM\Column(type: 'string', length: 255)]
    private string $imageFilename;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $shopUrl;

    #[ORM\Column(type: 'boolean')]
    private bool $isAvailable;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToMany(targetEntity: Category::class)]
    #[ORM\JoinTable(name: 'artwork_category')]
    private Collection $categories;

    #[ORM\ManyToMany(targetEntity: Tag::class)]
    #[ORM\JoinTable(name: 'artwork_tag')]
    private Collection $tags;

    private array $domainEvents = [];

    private function __construct(
        ArtworkId $id,
        ArtworkTitle $title,
        ?string $description,
        Technique $technique,
        Dimensions $dimensions,
        ArtworkYear $year,
        ?Price $price,
        string $imageFilename,
        ?string $shopUrl,
        bool $isAvailable,
        int $sortOrder,
    ) {
        $this->id            = $id->value();
        $this->title         = $title->value();
        $this->description   = $description;
        $this->technique     = $technique->value();
        $this->dimensions    = $dimensions->value();
        $this->year          = $year->value();
        $this->price         = $price?->value() !== null ? (string) $price->value() : null;
        $this->imageFilename = $imageFilename;
        $this->shopUrl       = $shopUrl;
        $this->isAvailable   = $isAvailable;
        $this->sortOrder     = $sortOrder;
        $this->createdAt     = new \DateTimeImmutable();
        $this->categories    = new ArrayCollection();
        $this->tags          = new ArrayCollection();
    }

    public static function create(
        ArtworkId $id,
        ArtworkTitle $title,
        ?string $description,
        Technique $technique,
        Dimensions $dimensions,
        ArtworkYear $year,
        ?Price $price,
        string $imageFilename,
        ?string $shopUrl,
        bool $isAvailable,
        int $sortOrder,
    ): self {
        $artwork = new self(
            $id, $title, $description, $technique,
            $dimensions, $year, $price, $imageFilename,
            $shopUrl, $isAvailable, $sortOrder,
        );

        $artwork->domainEvents[] = ArtworkCreated::create($id->value());

        return $artwork;
    }

    public function update(
        ArtworkTitle $title,
        ?string $description,
        Technique $technique,
        Dimensions $dimensions,
        ArtworkYear $year,
        ?Price $price,
        ?string $shopUrl,
        bool $isAvailable,
    ): void {
        $this->title       = $title->value();
        $this->description = $description;
        $this->technique   = $technique->value();
        $this->dimensions  = $dimensions->value();
        $this->year        = $year->value();
        $this->price       = $price?->value() !== null ? (string) $price->value() : null;
        $this->shopUrl     = $shopUrl;
        $this->isAvailable = $isAvailable;

        $this->domainEvents[] = ArtworkUpdated::create($this->id);
    }

    public function updateImage(string $imageFilename): void
    {
        $this->imageFilename = $imageFilename;
    }

    public function markAsDeleted(): void
    {
        $this->domainEvents[] = ArtworkDeleted::create($this->id);
    }

    public function assignCategory(Category $category): void
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }
    }

    public function removeCategory(Category $category): void
    {
        $this->categories->removeElement($category);
    }

    public function assignTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    public function pullDomainEvents(): array
    {
        $events            = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    public function id(): ArtworkId
    {
        return ArtworkId::create($this->id);
    }

    public function title(): ArtworkTitle
    {
        return ArtworkTitle::create($this->title);
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function technique(): Technique
    {
        return Technique::create($this->technique);
    }

    public function dimensions(): Dimensions
    {
        return Dimensions::create($this->dimensions);
    }

    public function year(): ArtworkYear
    {
        return ArtworkYear::create($this->year);
    }

    public function price(): ?Price
    {
        return $this->price !== null ? Price::create((float) $this->price) : null;
    }

    public function imageFilename(): string
    {
        return $this->imageFilename;
    }

    public function shopUrl(): ?string
    {
        return $this->shopUrl;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function categories(): Collection
    {
        return $this->categories;
    }

    public function tags(): Collection
    {
        return $this->tags;
    }
}
