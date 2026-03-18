# Steps: Implementación HalfyShop

## Criterios Técnicos Globales

- Constructores privados, acceso por factory method público estático: `ClassName::create(...)`
- Servicios de aplicación con método `execute()` legible — la lógica va en métodos privados con nombres descriptivos del flujo
- `declare(strict_types=1)` en todos los ficheros
- Value Objects inmutables con validación en constructor
- Domain Events con `occurredOn: DateTimeImmutable`
- Getters sin prefijo `get`

**Ejemplo de patrón constructor:**
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

**Ejemplo de patrón servicio:**
```php
final class CreateArtworkService
{
    public function execute(CreateArtworkCommand $command): void
    {
        $artwork   = $this->buildArtwork($command);
        $this->processAndStoreImage($command->imageFile(), $artwork->id());
        $this->save($artwork);
        $this->dispatchEvents($artwork);
    }

    private function buildArtwork(CreateArtworkCommand $command): Artwork { ... }
    private function processAndStoreImage(UploadedFile $file, ArtworkId $id): string { ... }
    private function save(Artwork $artwork): void { ... }
    private function dispatchEvents(Artwork $artwork): void { ... }
}
```

---

## Fase 1 — Docker e Infraestructura

### Paso 1.1 — Estructura de carpetas y Docker

**Archivos creados:**
- `docker-compose.yml`
- `docker/php/Dockerfile`
- `docker/nginx/default.conf`
- `app/.env`
- `app/.env.test`

**`docker-compose.yml`** — servicios:
- `php`: PHP 8.4-FPM Alpine, build `./docker/php`, volumen `./app:/var/www/html`
- `php-cli`: mismo build que `php`, comando `tail -f /dev/null` — para ejecutar comandos de consola (sync, migrations...)
- `nginx`: imagen nginx:alpine, puerto `8080:80`, volumen `./docker/nginx/default.conf`
- `postgres`: PostgreSQL 16, puerto `5432`, BD `halfyshop`, usuario/pass en `.env`, health check, volumen persistente `postgres_data`
- `postgres-test`: PostgreSQL 16, puerto `5433`, BD `halfyshop_test`, health check, volumen persistente `postgres_test_data`

**`docker/php/Dockerfile`** — extensiones PHP:
`pdo_pgsql`, `intl`, `bcmath`, `opcache`, `zip`, `exif`, `gd`

**`app/.env`**
```
DATABASE_URL=postgresql://user:pass@postgres:5432/halfyshop
DATABASE_TEST_URL=postgresql://user:pass@postgres-test:5433/halfyshop_test
CONTACT_EMAIL=anna@example.com
```

---

### Paso 1.2 — Proyecto Symfony

```bash
composer create-project symfony/skeleton app
cd app
composer require twig doctrine orm-pack security-bundle
composer require --dev phpunit symfony/test-pack
```

**Configurar rutas de templates** en `config/packages/twig.yaml`:
```yaml
twig:
    default_path: '%kernel.project_dir%/src/Infrastructure/Http/View'
```

**Namespace base** en `composer.json`:
```json
"autoload": {
    "psr-4": { "App\\": "src/" }
}
```

---

## Fase 2 — Capa de Dominio

### Paso 2.1 — Shared

**`src/Domain/Shared/ValueObject/Uuid.php`**
```php
abstract class Uuid
{
    private function __construct(private readonly string $value) {}

    public static function create(string $value): static
    // Valida formato UUID v4
    public static function generate(): static
    // Genera nuevo UUID v4 con ramsey/uuid
    public function value(): string
    public function equals(self $other): bool
}
```

**`src/Domain/Shared/Event/DomainEvent.php`**
```php
abstract class DomainEvent
{
    private function __construct(
        private readonly string $aggregateId,
        private readonly \DateTimeImmutable $occurredOn,
    ) {}

    public static function create(string $aggregateId): static
    public function aggregateId(): string
    public function occurredOn(): \DateTimeImmutable
}
```

---

### Paso 2.2 — Dominio Artwork

**Value Objects** en `src/Domain/Artwork/ValueObject/`:

| Archivo | Validación en `create()` |
|---|---|
| `ArtworkId.php` extends `Uuid` | — |
| `ArtworkTitle.php` | No vacío, máx 255 chars |
| `Technique.php` | No vacío, máx 100 chars |
| `Dimensions.php` | No vacío, máx 50 chars |
| `ArtworkYear.php` | Entre 1900 y año actual |
| `Price.php` | >= 0, máx 2 decimales |

Todos con:
```php
private function __construct(private readonly string|int|float $value) {}
public static function create(...): self
public function value(): string|int|float
```

**`src/Domain/Artwork/Entity/Artwork.php`**
```php
final class Artwork
{
    private array $domainEvents = [];

    private function __construct(
        private ArtworkId $id,
        private ArtworkTitle $title,
        private ?string $description,
        private Technique $technique,
        private Dimensions $dimensions,
        private ArtworkYear $year,
        private ?Price $price,
        private string $imageFilename,
        private ?string $shopUrl,
        private bool $isAvailable,
        private int $sortOrder,
        private \DateTimeImmutable $createdAt,
        private Collection $categories,   // ArrayCollection
        private Collection $tags,         // ArrayCollection
    ) {}

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
    ): self
    // Crea la entidad, inicializa collections, registra ArtworkCreated

    public function update(
        ArtworkTitle $title,
        ?string $description,
        Technique $technique,
        Dimensions $dimensions,
        ArtworkYear $year,
        ?Price $price,
        ?string $shopUrl,
        bool $isAvailable,
    ): void
    // Actualiza campos, registra ArtworkUpdated

    public function updateImage(string $imageFilename): void

    public function assignCategory(Category $category): void
    public function removeCategory(Category $category): void
    public function assignTag(Tag $tag): void
    public function removeTag(Tag $tag): void

    // Getters
    public function id(): ArtworkId
    public function title(): ArtworkTitle
    public function description(): ?string
    public function technique(): Technique
    public function dimensions(): Dimensions
    public function year(): ArtworkYear
    public function price(): ?Price
    public function imageFilename(): string
    public function shopUrl(): ?string
    public function isAvailable(): bool
    public function sortOrder(): int
    public function createdAt(): \DateTimeImmutable
    public function categories(): Collection
    public function tags(): Collection

    public function pullDomainEvents(): array
    // Retorna y vacía $domainEvents
}
```

**Domain Events** en `src/Domain/Artwork/Event/`:
- `ArtworkCreated.php` extends `DomainEvent` — sin campos extra
- `ArtworkUpdated.php` extends `DomainEvent` — sin campos extra
- `ArtworkDeleted.php` extends `DomainEvent` — sin campos extra

**`src/Domain/Artwork/Repository/ArtworkRepository.php`** (interface)
```php
interface ArtworkRepository
{
    public function save(Artwork $artwork): void;
    public function delete(Artwork $artwork): void;
    public function findById(ArtworkId $id): ?Artwork;
    public function findAll(): array;
    public function findByCategory(CategoryId $id): array;
    public function findNextSortOrder(): int;
}
```

---

### Paso 2.3 — Dominio Category

**Value Objects** en `src/Domain/Category/ValueObject/`:
- `CategoryId.php` extends `Uuid`
- `CategoryName.php` — no vacío, máx 100 chars
- `CategorySlug.php` — solo letras minúsculas, números y guiones

**`src/Domain/Category/Entity/Category.php`**
```php
final class Category
{
    private function __construct(
        private CategoryId $id,
        private CategoryName $name,
        private CategorySlug $slug,
        private int $sortOrder,
    ) {}

    public static function create(
        CategoryId $id,
        CategoryName $name,
        CategorySlug $slug,
        int $sortOrder,
    ): self

    public function update(CategoryName $name, CategorySlug $slug, int $sortOrder): void

    public function id(): CategoryId
    public function name(): CategoryName
    public function slug(): CategorySlug
    public function sortOrder(): int
}
```

**`src/Domain/Category/Repository/CategoryRepository.php`** (interface)
```php
interface CategoryRepository
{
    public function save(Category $category): void;
    public function delete(Category $category): void;
    public function findById(CategoryId $id): ?Category;
    public function findAll(): array;
    public function findBySlug(CategorySlug $slug): ?Category;
}
```

---

### Paso 2.4 — Dominio Tag

**Value Objects** en `src/Domain/Tag/ValueObject/`:
- `TagId.php` extends `Uuid`
- `TagName.php` — no vacío, máx 50 chars, lowercase

**`src/Domain/Tag/Entity/Tag.php`**
```php
final class Tag
{
    private function __construct(
        private TagId $id,
        private TagName $name,
        private string $slug,
    ) {}

    public static function create(TagId $id, TagName $name, string $slug): self
    public function update(TagName $name, string $slug): void
    public function id(): TagId
    public function name(): TagName
    public function slug(): string
}
```

**`src/Domain/Tag/Repository/TagRepository.php`** (interface)
```php
interface TagRepository
{
    public function save(Tag $tag): void;
    public function delete(Tag $tag): void;
    public function findById(TagId $id): ?Tag;
    public function findAll(): array;
}
```

---

### Paso 2.5 — Dominio Blog

**Value Objects** en `src/Domain/Blog/ValueObject/`:
- `PostId.php` extends `Uuid`
- `PostTitle.php` — no vacío, máx 255 chars
- `PostSlug.php` — solo letras minúsculas, números y guiones

**`src/Domain/Blog/Entity/Post.php`**
```php
final class Post
{
    private function __construct(
        private PostId $id,
        private PostTitle $title,
        private PostSlug $slug,
        private string $content,
        private ?\DateTimeImmutable $publishedAt,
        private \DateTimeImmutable $createdAt,
    ) {}

    public static function create(
        PostId $id,
        PostTitle $title,
        PostSlug $slug,
        string $content,
        ?\DateTimeImmutable $publishedAt,
    ): self

    public function update(PostTitle $title, PostSlug $slug, string $content, ?\DateTimeImmutable $publishedAt): void
    public function isPublished(): bool  // publishedAt !== null && publishedAt <= now

    public function id(): PostId
    public function title(): PostTitle
    public function slug(): PostSlug
    public function content(): string
    public function publishedAt(): ?\DateTimeImmutable
    public function createdAt(): \DateTimeImmutable
}
```

**`src/Domain/Blog/Repository/PostRepository.php`** (interface)
```php
interface PostRepository
{
    public function save(Post $post): void;
    public function delete(Post $post): void;
    public function findById(PostId $id): ?Post;
    public function findPublished(): array;
    public function findBySlug(PostSlug $slug): ?Post;
    public function findAll(): array;
}
```

---

### Paso 2.6 — Dominio About

**`src/Domain/About/ValueObject/AboutPageId.php`** extends `Uuid`

**`src/Domain/About/Entity/AboutPage.php`**
```php
final class AboutPage
{
    private function __construct(
        private AboutPageId $id,
        private string $content,
        private ?string $photoFilename,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public static function create(AboutPageId $id, string $content, ?string $photoFilename): self
    public function update(string $content, ?string $photoFilename): void
    // Actualiza campos y renueva updatedAt

    public function id(): AboutPageId
    public function content(): string
    public function photoFilename(): ?string
    public function updatedAt(): \DateTimeImmutable
}
```

**`src/Domain/About/Repository/AboutPageRepository.php`** (interface)
```php
interface AboutPageRepository
{
    public function save(AboutPage $page): void;
    public function find(): ?AboutPage;  // singleton, no necesita ID
}
```

---

### Paso 2.7 — Dominio Sync

**`src/Domain/Sync/Entity/SyncLog.php`**
```php
final class SyncLog
{
    private function __construct(
        private string $id,
        private \DateTimeImmutable $executedAt,
        private int $created,
        private int $updated,
        private int $unchanged,
        private string $log,       // texto con el detalle línea a línea
    ) {}

    public static function create(
        string $id,
        int $created,
        int $updated,
        int $unchanged,
        string $log,
    ): self

    public function id(): string
    public function executedAt(): \DateTimeImmutable
    public function created(): int
    public function updated(): int
    public function unchanged(): int
    public function log(): string
}
```

**`src/Domain/Sync/Repository/SyncLogRepository.php`** (interface)
```php
interface SyncLogRepository
{
    public function save(SyncLog $log): void;
    public function findLatest(): ?SyncLog;
}
```

---

## Fase 3 — Capa de Aplicación

### Paso 3.1 — Artwork Commands & Services

**`src/Application/Artwork/Create/CreateArtworkCommand.php`**
```php
final class CreateArtworkCommand
{
    private function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $technique,
        public readonly string $dimensions,
        public readonly int $year,
        public readonly ?float $price,
        public readonly UploadedFile $imageFile,
        public readonly ?string $shopUrl,
        public readonly bool $isAvailable,
        public readonly array $categoryIds,
        public readonly array $tagIds,
    ) {}

    public static function create(...): self
}
```

**`src/Application/Artwork/Create/CreateArtworkService.php`**
```php
final class CreateArtworkService
{
    public function __construct(
        private ArtworkRepository $artworkRepository,
        private CategoryRepository $categoryRepository,
        private TagRepository $tagRepository,
        private ImageProcessor $imageProcessor,
        private EventDispatcher $dispatcher,
    ) {}

    public function execute(CreateArtworkCommand $command): void
    {
        $imageFilename = $this->processAndStoreImage($command->imageFile);
        $artwork       = $this->buildArtwork($command, $imageFilename);
        $this->assignCategoriesAndTags($artwork, $command->categoryIds, $command->tagIds);
        $this->save($artwork);
        $this->dispatchEvents($artwork);
    }

    private function processAndStoreImage(UploadedFile $file): string
    private function buildArtwork(CreateArtworkCommand $command, string $imageFilename): Artwork
    private function assignCategoriesAndTags(Artwork $artwork, array $categoryIds, array $tagIds): void
    private function save(Artwork $artwork): void
    private function dispatchEvents(Artwork $artwork): void
}
```

**`src/Application/Artwork/Update/UpdateArtworkCommand.php`**
```php
final class UpdateArtworkCommand
{
    private function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $technique,
        public readonly string $dimensions,
        public readonly int $year,
        public readonly ?float $price,
        public readonly ?UploadedFile $imageFile,  // null = no cambia imagen
        public readonly ?string $shopUrl,
        public readonly bool $isAvailable,
        public readonly array $categoryIds,
        public readonly array $tagIds,
    ) {}

    public static function create(...): self
}
```

**`src/Application/Artwork/Update/UpdateArtworkService.php`**
```php
public function execute(UpdateArtworkCommand $command): void
{
    $artwork = $this->findArtworkOrFail($command->id);
    $this->updateArtworkData($artwork, $command);
    $this->updateImageIfProvided($artwork, $command->imageFile);
    $this->syncCategoriesAndTags($artwork, $command->categoryIds, $command->tagIds);
    $this->save($artwork);
    $this->dispatchEvents($artwork);
}
```

**`src/Application/Artwork/Delete/DeleteArtworkCommand.php`**
```php
final class DeleteArtworkCommand
{
    private function __construct(public readonly string $id) {}
    public static function create(string $id): self
}
```

**`src/Application/Artwork/Delete/DeleteArtworkService.php`**
```php
public function execute(DeleteArtworkCommand $command): void
{
    $artwork = $this->findArtworkOrFail($command->id);
    $this->deleteImage($artwork->imageFilename());
    $this->delete($artwork);
    $this->dispatchEvents($artwork);
}
```

**`src/Application/Artwork/Reorder/ReorderArtworksCommand.php`**
```php
final class ReorderArtworksCommand
{
    // $orderedIds = ['uuid1', 'uuid2', ...] en el nuevo orden
    private function __construct(public readonly array $orderedIds) {}
    public static function create(array $orderedIds): self
}
```

**`src/Application/Artwork/Reorder/ReorderArtworksService.php`**
```php
public function execute(ReorderArtworksCommand $command): void
{
    $artworks = $this->findAllArtworks($command->orderedIds);
    $this->applySortOrder($artworks, $command->orderedIds);
    $this->saveAll($artworks);
}
```

---

### Paso 3.2 — Category / Tag / Blog Commands & Services

Mismo patrón para cada dominio. Métodos `execute()` legibles:

**Category:**
```php
// CreateCategoryService
public function execute(CreateCategoryCommand $command): void
{
    $category = $this->buildCategory($command);
    $this->save($category);
}

// UpdateCategoryService
public function execute(UpdateCategoryCommand $command): void
{
    $category = $this->findCategoryOrFail($command->id);
    $this->updateCategoryData($category, $command);
    $this->save($category);
}

// DeleteCategoryService
public function execute(DeleteCategoryCommand $command): void
{
    $category = $this->findCategoryOrFail($command->id);
    $this->delete($category);
}
```

**Tag:** ídem patrón Category.

**Blog:**
```php
// CreatePostService
public function execute(CreatePostCommand $command): void
{
    $post = $this->buildPost($command);
    $this->save($post);
}

// UpdatePostService
public function execute(UpdatePostCommand $command): void
{
    $post = $this->findPostOrFail($command->id);
    $this->updatePostData($post, $command);
    $this->save($post);
}
```

---

### Paso 3.3 — About Service

**`src/Application/About/UpdateAboutCommand.php`**
```php
final class UpdateAboutCommand
{
    private function __construct(
        public readonly string $content,
        public readonly ?UploadedFile $photoFile,
    ) {}

    public static function create(string $content, ?UploadedFile $photoFile): self
}
```

**`src/Application/About/UpdateAboutService.php`**
```php
public function execute(UpdateAboutCommand $command): void
{
    $page = $this->findOrCreateAboutPage();
    $this->updatePhotoIfProvided($page, $command->photoFile);
    $this->updateContent($page, $command->content);
    $this->save($page);
}
```

---

### Paso 3.4 — Sync Service

**`src/Application/Sync/SyncWithBigCartelService.php`**
```php
public function execute(): SyncLog
{
    $items         = $this->fetchItemsFromFeed();
    $results       = $this->processItems($items);
    $log           = $this->buildSyncLog($results);
    $this->saveSyncLog($log);
    return $log;
}

private function fetchItemsFromFeed(): array
// Parsea products.xml de Big Cartel

private function processItems(array $items): array
// Retorna ['created' => n, 'updated' => n, 'unchanged' => n, 'log' => '...']

private function processItem(array $item): string
// Retorna 'created' | 'updated' | 'unchanged'
// Match por shopUrl, descarga imagen, crea/actualiza Artwork

private function buildSyncLog(array $results): SyncLog
private function saveSyncLog(SyncLog $log): void
```

---

### Paso 3.5 — ImageProcessor (servicio de infraestructura, interface en aplicación)

**`src/Application/Shared/ImageProcessor.php`** (interface)
```php
interface ImageProcessor
{
    public function process(UploadedFile $file, string $destinationDir): string;
    // Retorna el filename resultante
    // Genera thumbnail (600×400) y versión lightbox (1400px ancho)
    // Warning (no excepción) si supera 10MB
    // Guarda como JPEG calidad 85
}
```

**`src/Infrastructure/Image/GdImageProcessor.php`** implementa `ImageProcessor`

---

## Fase 4 — Infraestructura: Persistencia

### Paso 4.1 — Repositorios Doctrine

Un fichero por dominio en `src/Infrastructure/Persistence/Doctrine/`:

- `DoctrineArtworkRepository.php` implements `ArtworkRepository`
- `DoctrineCategoryRepository.php` implements `CategoryRepository`
- `DoctrineTagRepository.php` implements `TagRepository`
- `DoctrinePostRepository.php` implements `PostRepository`
- `DoctrineAboutPageRepository.php` implements `AboutPageRepository`
- `DoctrineSyncLogRepository.php` implements `SyncLogRepository`

Todos extienden `ServiceEntityRepository` de Doctrine.

### Paso 4.2 — Migrations

```bash
php bin/console doctrine:migrations:generate
php bin/console doctrine:migrations:migrate
```

Tablas: `artworks`, `categories`, `tags`, `posts`, `about_page`, `sync_logs`, `artwork_category` (pivot), `artwork_tag` (pivot)

---

## Fase 5 — Infraestructura: Controllers

### Paso 5.1 — Controllers Públicos

**`src/Infrastructure/Http/Controller/Public/HomeController.php`**
- `index(): Response` — GET `/`
- Pasa al template: todas las obras, todas las categorías

**`src/Infrastructure/Http/Controller/Public/GalleryController.php`**
- `byCategory(string $slug): Response` — GET `/categoria/{slug}`
- Pasa al template: obras filtradas, categoría activa, todas las categorías

**`src/Infrastructure/Http/Controller/Public/BlogController.php`**
- `index(): Response` — GET `/blog/`
- `show(string $slug): Response` — GET `/blog/{slug}/`

**`src/Infrastructure/Http/Controller/Public/AboutController.php`**
- `index(): Response` — GET `/sobre-mi/`
- Pasa al template: AboutPage, email de contacto desde `$_ENV['CONTACT_EMAIL']`

### Paso 5.2 — Controllers Admin

**`src/Infrastructure/Http/Controller/Admin/AdminAuthController.php`**
- `login(): Response` — GET `/admin/login`
- `logout(): void` — GET `/admin/logout`

**`src/Infrastructure/Http/Controller/Admin/AdminArtworkController.php`**
- `index(): Response` — GET `/admin/artworks`
- `new(): Response` — GET `/admin/artworks/new`
- `create(Request $request): Response` — POST `/admin/artworks`
- `edit(string $id): Response` — GET `/admin/artworks/{id}/edit`
- `update(string $id, Request $request): Response` — POST `/admin/artworks/{id}`
- `delete(string $id, Request $request): Response` — POST `/admin/artworks/{id}/delete`
- `reorder(Request $request): JsonResponse` — POST `/admin/artworks/reorder`

**`src/Infrastructure/Http/Controller/Admin/AdminCategoryController.php`**
- `index()`, `new()`, `create()`, `edit()`, `update()`, `delete()`

**`src/Infrastructure/Http/Controller/Admin/AdminTagController.php`**
- `index()`, `new()`, `create()`, `edit()`, `update()`, `delete()`

**`src/Infrastructure/Http/Controller/Admin/AdminPostController.php`**
- `index()`, `new()`, `create()`, `edit()`, `update()`, `delete()`

**`src/Infrastructure/Http/Controller/Admin/AdminAboutController.php`**
- `edit(): Response` — GET `/admin/about`
- `update(Request $request): Response` — POST `/admin/about`

**`src/Infrastructure/Http/Controller/Admin/AdminSyncController.php`**
- `index(): Response` — GET `/admin/sync`
- `sync(Request $request): Response` — POST `/admin/sync`

---

## Fase 6 — Templates (Twig)

Ubicación base: `src/Infrastructure/Http/View/`

### Templates públicos

```
public/
├── layout.html.twig              (base: nav, fondo, footer)
├── home/
│   └── index.html.twig           (grid de obras)
├── gallery/
│   └── by_category.html.twig     (grid filtrado)
├── blog/
│   ├── index.html.twig           (listado entradas)
│   └── show.html.twig            (entrada individual)
└── about/
    └── index.html.twig           (sobre mí + mailto)
```

### Templates admin

```
admin/
├── layout.html.twig              (base admin: nav lateral)
├── artwork/
│   ├── index.html.twig           (listado + drag&drop)
│   ├── form.html.twig            (formulario crear/editar)
├── category/
│   ├── index.html.twig
│   └── form.html.twig
├── tag/
│   ├── index.html.twig
│   └── form.html.twig
├── post/
│   ├── index.html.twig
│   └── form.html.twig
├── about/
│   └── form.html.twig
└── sync/
    └── index.html.twig           (botón sync + última ejecución + log)
```

---

## Fase 7 — Frontend Público (CSS + JS)

Archivos en `public/assets/`:

```
public/assets/
├── css/
│   ├── main.css                  (variables, reset, tipografías, layout)
│   ├── grid.css                  (grid responsive de obras)
│   ├── lightbox.css              (personalización GLightbox)
│   ├── nav.css                   (navegación)
│   └── admin.css                 (backoffice)
├── js/
│   ├── grid-animations.js        (GSAP: entrada en scroll, hover)
│   ├── lightbox.js               (GLightbox init + customización)
│   ├── category-filter.js        (filtro categorías con transición)
│   └── admin-sortable.js         (SortableJS drag&drop backoffice)
└── img/
    └── background.jpg            (imagen de fondo semitransparente, asset estático)
```

**`grid-animations.js`** — funciones:
- `initScrollAnimations()` — GSAP ScrollTrigger, fade-in por filas
- `initHoverEffects()` — overlay al hover sobre cada thumbnail

**`lightbox.js`** — funciones:
- `initLightbox()` — GLightbox con fondo blanco semitransparente
- `buildLightboxContent(artworkData)` — construye HTML con título, técnica, dimensiones, año, categorías, tags, precio, botón tienda

**`category-filter.js`** — funciones:
- `initCategoryFilter()` — filtra el grid por categoría con transición GSAP

---

## Fase 8 — Tests

### Unitarios (`tests/Unit/`)

**Value Objects** — un test por Value Object:
- `ArtworkTitleTest` — test vacío, test muy largo, test válido
- `PriceTest` — test negativo, test decimales excesivos, test válido
- `ArtworkYearTest` — test año futuro, test año válido
- (ídem resto de VOs)

**Entidades:**
- `ArtworkTest` — `create()` genera evento `ArtworkCreated`, `update()` genera `ArtworkUpdated`, `pullDomainEvents()` vacía el array

**Servicios de aplicación** (repositorios mockeados con PHPUnit mocks):
- `CreateArtworkServiceTest`
- `UpdateArtworkServiceTest`
- `DeleteArtworkServiceTest`
- `ReorderArtworksServiceTest`
- `SyncWithBigCartelServiceTest`

### Integración (`tests/Integration/`)

**Base: `tests/Integration/IntegrationTestCase.php`**
```php
abstract class IntegrationTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->getConnection()->rollBack();
        parent::tearDown();
    }
}
```

**Repositorios** contra PostgreSQL de test:
- `DoctrineArtworkRepositoryTest` — save, findById, findByCategory, delete
- `DoctrineCategoryRepositoryTest`
- `DoctrineTagRepositoryTest`
- `DoctrinePostRepositoryTest`
- `DoctrineAboutPageRepositoryTest`

**Controllers públicos:**
- `HomeControllerTest` — GET `/` responde 200
- `GalleryControllerTest` — GET `/categoria/{slug}` responde 200 o 404
- `BlogControllerTest` — GET `/blog/` responde 200
- `AboutControllerTest` — GET `/sobre-mi/` responde 200

**Controllers admin:**
- `AdminArtworkControllerTest` — rutas protegidas redirigen a login sin auth, CRUD responde correctamente con auth

---

## Fase 9 — CI (GitHub Actions)

**`.github/workflows/ci.yml`**

```yaml
name: CI
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    services:
      postgres-test:
        image: postgres:16
        env:
          POSTGRES_DB: halfyshop_test
          POSTGRES_USER: user
          POSTGRES_PASSWORD: pass
        ports: ['5433:5432']
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: pdo_pgsql, intl, bcmath, zip, exif, gd
      - run: composer install --no-interaction
      - run: php bin/console doctrine:migrations:migrate --env=test --no-interaction
      - run: php bin/phpunit
```
