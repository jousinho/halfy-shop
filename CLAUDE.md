# HalfyShop — Convenciones del Proyecto

## Stack

- PHP 8.4 + Symfony 8.0
- PostgreSQL 16
- Doctrine ORM 3.x con attribute mapping
- PHPUnit para tests

## Convenciones de Código

- `declare(strict_types=1)` en todos los ficheros PHP
- Constructores privados, acceso por factory method público estático: `ClassName::create(...)`
- Getters sin prefijo `get`: `title()`, `isAvailable()`, `price()`
- Setters con prefijo `set`
- Value Objects inmutables con validación en constructor
- Domain Events con `occurredOn: DateTimeImmutable`
- Sin comentarios salvo lógica no obvia

## Patrón Servicio de Aplicación

El método `execute()` debe ser legible — la lógica va en métodos privados con nombres que describan el flujo:

```php
public function execute(CreateArtworkCommand $command): void
{
    $imageFilename = $this->processAndStoreImage($command->imageFile);
    $artwork       = $this->buildArtwork($command, $imageFilename);
    $this->assignCategoriesAndTags($artwork, $command->categoryIds, $command->tagIds);
    $this->save($artwork);
    $this->dispatchEvents($artwork);
}
```

## Patrón Constructor

```php
final class ArtworkTitle
{
    private function __construct(private readonly string $value) {}

    public static function create(string $value): self
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }
        return new self(trim($value));
    }

    public function value(): string { return $this->value; }
}
```

## Convención de Nombres en Tests

```
test_{acción}_{contexto}__when_{condición}__should_{resultado}
```

Ejemplo: `test_create_artwork__when_title_is_empty__should_throw_exception`

## Tests de Integración

Usar `IntegrationTestCase` con `beginTransaction()` / `rollBack()` — nunca limpiar la BD manualmente entre tests.

## Arquitectura

```
src/
├── Domain/          # Lógica de negocio pura. Sin dependencias de infraestructura.
│   └── {Contexto}/
│       ├── Entity/
│       ├── Repository/   # Solo interfaces
│       ├── ValueObject/
│       └── Event/
├── Application/     # Orquestación. Commands + Services. Sin lógica de negocio.
└── Infrastructure/  # Doctrine, Controllers, Http, Views, Image processing.
```

Los templates Twig están en `src/Infrastructure/Http/View/`, no en `templates/`.

## Comandos Útiles

```bash
# Levantar entorno
docker compose up -d

# Ejecutar comandos Symfony
docker compose exec php-cli php bin/console <comando>

# Migrations
docker compose exec php-cli php bin/console doctrine:migrations:migrate

# Tests
docker compose exec php-cli php bin/phpunit

# Sync con Big Cartel
docker compose exec php-cli php bin/console app:sync:bigcartel
```
