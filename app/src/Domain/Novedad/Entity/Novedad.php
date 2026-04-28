<?php

declare(strict_types=1);

namespace App\Domain\Novedad\Entity;

use App\Domain\Novedad\Event\NovedadCreated;
use App\Domain\Novedad\Event\NovedadDeleted;
use App\Domain\Novedad\Event\NovedadUpdated;
use App\Domain\Novedad\ValueObject\NovedadId;
use App\Domain\Novedad\ValueObject\NovedadTipo;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'novedades')]
final class Novedad
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $titulo;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $tituloEn;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $contenido;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $contenidoEn;

    #[ORM\Column(type: 'string', length: 20, enumType: NovedadTipo::class)]
    private NovedadTipo $tipo;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $fecha;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $fechaFin;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imagen;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $lugar;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $url;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $videoYoutube;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $videoReel;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $slug;

    #[ORM\Column(type: 'boolean')]
    private bool $publicado;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    private array $domainEvents = [];

    private function __construct(
        NovedadId $id,
        string $titulo,
        ?string $tituloEn,
        ?string $contenido,
        ?string $contenidoEn,
        NovedadTipo $tipo,
        \DateTimeImmutable $fecha,
        ?\DateTimeImmutable $fechaFin,
        ?string $imagen,
        ?string $lugar,
        ?string $url,
        ?string $videoYoutube,
        ?string $videoReel,
        string $slug,
        bool $publicado,
    ) {
        $this->id           = $id->value();
        $this->titulo       = trim($titulo);
        $this->tituloEn     = $tituloEn !== null ? trim($tituloEn) ?: null : null;
        $this->contenido    = $contenido;
        $this->contenidoEn  = $contenidoEn;
        $this->tipo         = $tipo;
        if ($fechaFin !== null && $fechaFin < $fecha) {
            throw new \InvalidArgumentException('La fecha fin no puede ser anterior a la fecha inicio.');
        }

        $this->fecha         = $fecha;
        $this->fechaFin      = $fechaFin;
        $this->imagen        = $imagen;
        $this->lugar         = $lugar !== null ? trim($lugar) ?: null : null;
        $this->url           = $url !== null ? trim($url) ?: null : null;
        $this->videoYoutube  = $videoYoutube !== null ? trim($videoYoutube) ?: null : null;
        $this->videoReel     = $videoReel !== null ? trim($videoReel) ?: null : null;
        $this->slug          = $slug;
        $this->publicado     = $publicado;
        $this->createdAt     = new \DateTimeImmutable();
    }

    public static function create(
        NovedadId $id,
        string $titulo,
        ?string $tituloEn,
        ?string $contenido,
        ?string $contenidoEn,
        NovedadTipo $tipo,
        \DateTimeImmutable $fecha,
        ?\DateTimeImmutable $fechaFin,
        ?string $imagen,
        ?string $lugar,
        ?string $url,
        ?string $videoYoutube,
        ?string $videoReel,
        string $slug,
        bool $publicado,
    ): self {
        $novedad = new self(
            $id, $titulo, $tituloEn, $contenido, $contenidoEn,
            $tipo, $fecha, $fechaFin, $imagen, $lugar, $url, $videoYoutube, $videoReel, $slug, $publicado,
        );

        $novedad->domainEvents[] = NovedadCreated::create($id->value());

        return $novedad;
    }

    public function update(
        string $titulo,
        ?string $tituloEn,
        ?string $contenido,
        ?string $contenidoEn,
        NovedadTipo $tipo,
        \DateTimeImmutable $fecha,
        ?\DateTimeImmutable $fechaFin,
        ?string $imagen,
        ?string $lugar,
        ?string $url,
        ?string $videoYoutube,
        ?string $videoReel,
        string $slug,
        bool $publicado,
    ): void {
        $this->titulo        = trim($titulo);
        $this->tituloEn      = $tituloEn !== null ? trim($tituloEn) ?: null : null;
        $this->contenido     = $contenido;
        $this->contenidoEn   = $contenidoEn;
        $this->tipo          = $tipo;
        $this->fecha         = $fecha;
        $this->fechaFin      = $fechaFin;
        if ($imagen !== null) {
            $this->imagen = $imagen;
        }
        $this->lugar         = $lugar !== null ? trim($lugar) ?: null : null;
        $this->url           = $url !== null ? trim($url) ?: null : null;
        $this->videoYoutube  = $videoYoutube !== null ? trim($videoYoutube) ?: null : null;
        $this->videoReel     = $videoReel !== null ? trim($videoReel) ?: null : null;
        $this->slug          = $slug;
        $this->publicado     = $publicado;

        $this->domainEvents[] = NovedadUpdated::create($this->id);
    }

    public function markAsDeleted(): void
    {
        $this->domainEvents[] = NovedadDeleted::create($this->id);
    }

    public function pullDomainEvents(): array
    {
        $events             = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    public function id(): NovedadId
    {
        return NovedadId::create($this->id);
    }

    public function titulo(): string
    {
        return $this->titulo;
    }

    public function tituloEn(): ?string
    {
        return $this->tituloEn;
    }

    public function tituloForLocale(string $locale): string
    {
        return ($locale === 'en' && $this->tituloEn !== null && $this->tituloEn !== '')
            ? $this->tituloEn
            : $this->titulo;
    }

    public function contenido(): ?string
    {
        return $this->contenido;
    }

    public function contenidoEn(): ?string
    {
        return $this->contenidoEn;
    }

    public function contenidoForLocale(string $locale): ?string
    {
        return ($locale === 'en' && $this->contenidoEn !== null && $this->contenidoEn !== '')
            ? $this->contenidoEn
            : $this->contenido;
    }

    public function tipo(): NovedadTipo
    {
        return $this->tipo;
    }

    public function fecha(): \DateTimeImmutable
    {
        return $this->fecha;
    }

    public function fechaFin(): ?\DateTimeImmutable
    {
        return $this->fechaFin;
    }

    public function imagen(): ?string
    {
        return $this->imagen;
    }

    public function lugar(): ?string
    {
        return $this->lugar;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public function videoYoutube(): ?string
    {
        return $this->videoYoutube;
    }

    public function videoReel(): ?string
    {
        return $this->videoReel;
    }

    public function embedUrlYoutube(): ?string
    {
        if ($this->videoYoutube === null) {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $this->videoYoutube, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        return null;
    }

    public function embedUrlReel(): ?string
    {
        if ($this->videoReel === null) {
            return null;
        }

        if (preg_match('#instagram\.com/(?:reel|p)/([a-zA-Z0-9_-]+)#', $this->videoReel, $m)) {
            return 'https://www.instagram.com/p/' . $m[1] . '/embed/';
        }

        return null;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function publicado(): bool
    {
        return $this->publicado;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
