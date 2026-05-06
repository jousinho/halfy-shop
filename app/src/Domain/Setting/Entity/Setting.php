<?php

declare(strict_types=1);

namespace App\Domain\Setting\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'settings')]
class Setting
{
    #[ORM\Id]
    #[ORM\Column(name: 'setting_key', type: 'string', length: 100)]
    private string $key;

    #[ORM\Column(type: 'text')]
    private string $value;

    private function __construct(string $key, string $value)
    {
        $this->key   = $key;
        $this->value = $value;
    }

    public static function create(string $key, string $value): self
    {
        return new self($key, $value);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
