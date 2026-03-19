<?php

declare(strict_types=1);

namespace App\Domain\Sync\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sync_logs')]
final class SyncLog
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $executedAt;

    #[ORM\Column(type: 'integer')]
    private int $created;

    #[ORM\Column(type: 'integer')]
    private int $updated;

    #[ORM\Column(type: 'integer')]
    private int $unchanged;

    #[ORM\Column(type: 'text')]
    private string $log;

    private function __construct(
        string $id,
        int $created,
        int $updated,
        int $unchanged,
        string $log,
    ) {
        $this->id         = $id;
        $this->executedAt = new \DateTimeImmutable();
        $this->created    = $created;
        $this->updated    = $updated;
        $this->unchanged  = $unchanged;
        $this->log        = $log;
    }

    public static function create(
        string $id,
        int $created,
        int $updated,
        int $unchanged,
        string $log,
    ): self {
        return new self($id, $created, $updated, $unchanged, $log);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function executedAt(): \DateTimeImmutable
    {
        return $this->executedAt;
    }

    public function created(): int
    {
        return $this->created;
    }

    public function updated(): int
    {
        return $this->updated;
    }

    public function unchanged(): int
    {
        return $this->unchanged;
    }

    public function log(): string
    {
        return $this->log;
    }

    public function total(): int
    {
        return $this->created + $this->updated + $this->unchanged;
    }
}
