# Plan: HalfyShop — Portfolio Web para Anna Pownall

## Contexto

Rediseño del portfolio de Anna Pownall (grabado e ilustración). La web actual es un WordPress en http://annapownall.com — se clona estéticamente pero se reimplementa desde cero con el stack propuesto.

**Objetivos:**
1. Replicar la estética minimalista/serif de la web actual
2. Anna pueda gestionar su catálogo sin tocar código
3. Arquitectura limpia, testeable y preparada para crecer

---

## Stack Técnico

- **PHP 8.4** + **Symfony 8.0**
- **PostgreSQL 16** (producción) + **PostgreSQL de test** (Docker, integración)
- **Docker** (php-fpm + php-cli + nginx + postgres + postgres-test)
- **Twig** para frontend público y backoffice
- **Vanilla JS** + **GSAP** (animaciones) + **GLightbox** (lightbox personalizado)
- **Doctrine ORM 3.x** con attribute mapping
- **PHPUnit** para tests unitarios e integración

---

## Estética Visual (referencia: annapownall.com)

Inspirada en la web de Anna pero con layout propio fluido y responsive — no se copia el ancho fijo de 640px.

- Fondo blanco puro, imagen de obra como fondo estático semitransparente (no editable, asset estático)
- Tipografías: **Droid Serif** + **Georgia** (body), **Tinos** (logo)
- Paleta: blanco + grises azulados suaves (`#babecb`, `#a5a8b2`, `#313339`)
- Navegación horizontal centrada, mayúsculas, 12px
- Grid de obras responsive: 3 columnas (desktop) → 2 (tablet) → 1 (móvil)
- Overlay oscuro sobre thumbnails al hover
- **Lightbox**: fondo blanco semitransparente (no negro), imagen grande + datos de la obra
- CSS propio, sin frameworks — mobile-first

---

## Arquitectura: DDD

```
src/
├── Domain/
│   ├── Artwork/
│   │   ├── Entity/
│   │   │   └── Artwork.php              (Aggregate Root)
│   │   ├── Repository/
│   │   │   └── ArtworkRepository.php    (interface)
│   │   ├── ValueObject/
│   │   │   ├── ArtworkId.php
│   │   │   ├── ArtworkTitle.php
│   │   │   ├── Technique.php
│   │   │   ├── Dimensions.php
│   │   │   ├── ArtworkYear.php
│   │   │   └── Price.php
│   │   └── Event/
│   │       ├── ArtworkCreated.php
│   │       ├── ArtworkUpdated.php
│   │       └── ArtworkDeleted.php
│   ├── Category/
│   │   ├── Entity/
│   │   │   └── Category.php
│   │   ├── Repository/
│   │   │   └── CategoryRepository.php   (interface)
│   │   └── ValueObject/
│   │       ├── CategoryId.php
│   │       ├── CategoryName.php
│   │       └── CategorySlug.php
│   ├── Tag/
│   │   ├── Entity/
│   │   │   └── Tag.php
│   │   ├── Repository/
│   │   │   └── TagRepository.php        (interface)
│   │   └── ValueObject/
│   │       ├── TagId.php
│   │       └── TagName.php
│   ├── Blog/
│   │   ├── Entity/
│   │   │   └── Post.php
│   │   ├── Repository/
│   │   │   └── PostRepository.php       (interface)
│   │   └── ValueObject/
│   │       ├── PostId.php
│   │       ├── PostTitle.php
│   │       └── PostSlug.php
│   ├── About/
│   │   ├── Entity/
│   │   │   └── AboutPage.php
│   │   ├── Repository/
│   │   │   └── AboutPageRepository.php  (interface)
│   │   └── ValueObject/
│   │       └── AboutPageId.php
│   └── Shared/
│       ├── ValueObject/
│       │   └── Uuid.php
│       └── Event/
│           └── DomainEvent.php
├── Application/
│   ├── Artwork/
│   │   ├── Create/
│   │   │   ├── CreateArtworkCommand.php
│   │   │   └── CreateArtworkService.php
│   │   ├── Update/
│   │   │   ├── UpdateArtworkCommand.php
│   │   │   └── UpdateArtworkService.php
│   │   ├── Delete/
│   │   │   ├── DeleteArtworkCommand.php
│   │   │   └── DeleteArtworkService.php
│   │   └── Reorder/
│   │       ├── ReorderArtworksCommand.php
│   │       └── ReorderArtworksService.php
│   ├── Category/   (ídem: Create, Update, Delete)
│   ├── Tag/        (ídem: Create, Update, Delete)
│   ├── Blog/       (ídem: Create, Update, Delete)
│   └── About/
│       ├── UpdateAboutCommand.php
│       └── UpdateAboutService.php
└── Infrastructure/
    ├── Persistence/Doctrine/
    │   ├── DoctrineArtworkRepository.php
    │   ├── DoctrineCategoryRepository.php
    │   ├── DoctrineTagRepository.php
    │   ├── DoctrinePostRepository.php
    │   └── DoctrineAboutPageRepository.php
    └── Http/
        ├── Controller/
        │   ├── Public/
        │   │   ├── HomeController.php
        │   │   ├── GalleryController.php
        │   │   ├── BlogController.php
        │   │   └── AboutController.php
        │   └── Admin/
        │       ├── AdminAuthController.php
        │       ├── AdminArtworkController.php
        │       ├── AdminCategoryController.php
        │       ├── AdminTagController.php
        │       ├── AdminPostController.php
        │       └── AdminAboutController.php
        └── View/
            ├── public/
            │   ├── home/
            │   ├── gallery/
            │   ├── blog/
            │   └── about/
            └── admin/
                ├── artwork/
                ├── category/
                ├── tag/
                ├── post/
                └── about/
```

**Flujo de un command:**
```
Controller → CreateArtworkCommand (encapsula parámetros) → CreateArtworkService (procesa)
```

---

## Modelo de Datos

### `Artwork` (Aggregate Root)
| Campo | Tipo | Descripción |
|---|---|---|
| id | ArtworkId (UUID) | PK |
| title | ArtworkTitle | Título de la obra |
| description | text\|null | Descripción libre |
| technique | Technique | Técnica (ej: Fotopolímero, Acuarela) |
| dimensions | Dimensions | Dimensiones (ej: 35×37 cm) |
| year | ArtworkYear | Año de creación |
| price | Price\|null | Precio |
| imageFilename | string | Ruta en `uploads/artworks/` |
| shopUrl | string\|null | Link a tienda externa |
| isAvailable | bool | Disponibilidad |
| sortOrder | int | Orden manual en galería |
| createdAt | datetime | |
| **categories** | ManyToMany → Category | |
| **tags** | ManyToMany → Tag | |

### `Category`
| Campo | Tipo | Descripción |
|---|---|---|
| id | CategoryId (UUID) | PK |
| name | CategoryName | Nombre (ej: Grabados, Ilustraciones) |
| slug | CategorySlug | URL-friendly |
| sortOrder | int | Orden en filtros |

### `Tag`
| Campo | Tipo | Descripción |
|---|---|---|
| id | TagId (UUID) | PK |
| name | TagName | Nombre (ej: acuarela, naturaleza) |
| slug | string | URL-friendly |

### `Post` (Blog)
| Campo | Tipo | Descripción |
|---|---|---|
| id | PostId (UUID) | PK |
| title | PostTitle | Título |
| slug | PostSlug | URL-friendly |
| content | text | Contenido libre |
| publishedAt | datetime\|null | null = borrador |
| createdAt | datetime | |

### `AboutPage`
| Campo | Tipo | Descripción |
|---|---|---|
| id | AboutPageId (UUID) | PK (singleton) |
| content | text | Texto biográfico libre (HTML) |
| photoFilename | string\|null | Foto de Anna |
| updatedAt | datetime | |

---

## Rutas

### Web Pública
```
GET /                          → inicio (grid completo de obras)
GET /grabados/                 → grid filtrado por categoría
GET /ilustraciones/            → grid filtrado por categoría
GET /categoria/{slug}/         → grid filtrado por cualquier categoría
GET /blog/                     → listado de entradas (vacío de momento)
GET /blog/{slug}/              → entrada individual
GET /sobre-mi/                 → sobre mí + formulario de contacto
```

Las obras no tienen página propia — se abren en lightbox desde el grid.

### Backoffice (prefijo `/admin`)
```
GET  /admin/login
POST /admin/login
GET  /admin/logout

GET  /admin/artworks
GET  /admin/artworks/new
POST /admin/artworks
GET  /admin/artworks/{id}/edit
POST /admin/artworks/{id}
POST /admin/artworks/{id}/delete
POST /admin/artworks/reorder        (AJAX, drag & drop)

GET  /admin/categories
GET  /admin/categories/new
POST /admin/categories
GET  /admin/categories/{id}/edit
POST /admin/categories/{id}
POST /admin/categories/{id}/delete

GET  /admin/tags
GET  /admin/tags/new
POST /admin/tags
GET  /admin/tags/{id}/edit
POST /admin/tags/{id}
POST /admin/tags/{id}/delete

GET  /admin/posts
GET  /admin/posts/new
POST /admin/posts
GET  /admin/posts/{id}/edit
POST /admin/posts/{id}
POST /admin/posts/{id}/delete

GET  /admin/about
POST /admin/about
```

---

## Lightbox (web pública)

Al hacer click en una obra se abre GLightbox personalizado:
- Fondo blanco semitransparente (no negro)
- Imagen grande
- Título, técnica, dimensiones, año
- Categorías + tags
- Precio + estado disponibilidad
- Botón "Ver en tienda" (si tiene `shopUrl`)
- Sin obras relacionadas, sin comentarios

---

## Imágenes

**Subida manual (backoffice):**
- Límite: 10MB — si se supera, warning visible pero no se bloquea la subida
- Redimensionado automático al subir con extensión `gd`:
  - **Thumbnail** (grid): 600×400px
  - **Lightbox / detalle**: 1400px ancho máximo, alto proporcional
  - **Formato de salida**: JPEG, calidad 85%
- Las imágenes se guardan en `uploads/artworks/`

**Imágenes importadas desde Big Cartel (sync):**
- Se aplica el mismo procesado que la subida manual

---

## Sincronización con Big Cartel

Sección en el backoffice (`/admin/sync`) que permite importar obras desde la tienda de Anna en Big Cartel.

**Fuente:** feed RSS en `https://annapownall.bigcartel.com/products.xml`

**Datos que se importan por obra:**
- Título
- Descripción
- Precio
- URL de la obra en tienda (`shopUrl`)
- Imagen (descargada a `uploads/artworks/`)
- Disponibilidad

**Comportamiento:**
- No destructivo — si la obra ya existe (match por `shopUrl`) la actualiza, no la duplica
- Las obras nuevas se crean con `isAvailable` según el feed
- Las imágenes solo se descargan si han cambiado o son nuevas

**UI del backoffice:**
- Botón "Sincronizar con Big Cartel"
- Fecha y hora de última sincronización
- Nº de obras nuevas / actualizadas / sin cambios de la última sync
- Log de la última ejecución

**Dominio:**
- `SyncLog` — entidad que guarda el historial de sincronizaciones (fecha, resultado, contadores)

---

## SEO

Meta tags en todas las páginas públicas:
- `<title>` descriptivo por página
- `<meta name="description">` por página
- Open Graph: `og:title`, `og:description`, `og:image`, `og:url`
- `<link rel="canonical">` en cada página
- En el lightbox, la URL no cambia (no hay página propia por obra), por lo que og:image del inicio apunta a la imagen de fondo

---

## Formulario de Contacto

- Enlace `mailto:` con el email configurado en `.env` como `CONTACT_EMAIL`
- Sin servidor de correo ni Symfony Mailer

---

## Autenticación

- Symfony Security, usuario único admin definido en `.env.local`
- `InMemoryUserProvider` (sin tabla de usuarios en BD)
- Rutas `/admin/*` protegidas con `IS_AUTHENTICATED_FULLY`

---

## Tests

### Unitarios
- Todos los Value Objects (validaciones, excepciones)
- Toda la lógica de dominio en Aggregates
- Servicios de aplicación (con repositorios mockeados)
- Domain Events (que se disparan correctamente)

### Integración
- Base: `IntegrationTestCase` con `beginTransaction()` / `rollBack()` — aislamiento perfecto sin limpiar BD entre tests
- Repositorios Doctrine contra PostgreSQL de test (Docker)
- Controllers públicos y de admin (respuestas HTTP, redirecciones)
- Subida de imágenes
- Sync con Big Cartel (cliente HTTP mockeado, repositorios reales)

### Convención de nombres
```
test_{acción}_{contexto}__when_{condición}__should_{resultado}
```
Ejemplo: `test_create_artwork__when_title_is_empty__should_throw_exception`

---

## Docker

```
docker-compose.yml
├── php           (PHP 8.4-FPM Alpine) — extensiones: pdo_pgsql, intl, bcmath, opcache, zip, exif, gd
├── php-cli       (mismo build que php) — para comandos de consola (sync, migrations...)
├── nginx         (puerto 8080)
├── postgres      (PostgreSQL 16, puerto 5432, health check) — BD: halfyshop, volumen persistente
└── postgres-test (PostgreSQL 16, puerto 5433, health check) — BD: halfyshop_test, volumen persistente
```

---

## Convenciones de Código

- `declare(strict_types=1)` en todos los ficheros
- Aggregates con constructor `private` + factory method `create(): self`
- Getters sin prefijo `get`: `title()`, `technique()`, `isAvailable()`
- Value Objects inmutables con validación en constructor
- Domain Events con `occurredOn: DateTimeImmutable`
- Sin comentarios salvo lógica no obvia

---

## CI/CD

De momento solo CI. Pipeline con GitHub Actions que ejecuta en cada push/PR:

- Levantar servicios necesarios (PostgreSQL de test)
- Instalar dependencias (`composer install`)
- Ejecutar tests unitarios
- Ejecutar tests de integración contra la BD de test

CD queda fuera de scope por ahora.

---

## Notas Futuras (fuera de scope actual)

- CD (despliegue automático)
- Sistema de traducciones (i18n) — arquitectura preparada para añadirlo
- Selector de idioma en frontend
- Dashboard de admin con estadísticas de visitas
- Gestión de obras vendidas/eliminadas en la sync con Big Cartel
