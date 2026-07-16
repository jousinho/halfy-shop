# Banner de aviso — Planificación

## Estado
Pendiente. Todo revertido al último commit. El repo está limpio.

---

## Qué hay que hacer

### 1. Banner configurable desde el backoffice
- Toggle activo/inactivo
- Texto libre (respetando mayúsculas del usuario, sin wysiwyg)
- 5 colores de fondo suavizados con degradado radial (amber, sky, sage, rose, sand)
- Previsualizador en la página de Personalización (ancho parcial, ~420px)

### 2. Dónde aparece
- **Solo en la galería** (página principal y by_category)
- Permanente mientras scrolleas — dentro del header fijo, no en el contenido
- Debajo de la barra de navegación del header, encima del contenido

### 3. Settings a usar
Tabla `settings` con clave/valor, patrón ya existente:
- `announcement_enabled` → `0` / `1`
- `announcement_text` → string
- `announcement_color` → `amber` | `sky` | `sage` | `rose` | `sand`

---

## Qué tema está activo

El tema activo en producción es el **custom** (`themes/custom/`):
- Layout: `app/src/Infrastructure/Http/View/public/themes/custom/layout.html.twig`
- Header: `position: fixed; top: 0` → `.es-header`
- Row de nav: `.es-header__row` con `height: 90px` (desktop) / `52px` (mobile/scrolled)
- El JS solo togglea la clase `is-scrolled` en el header cuando `scrollY > 20`
- `.es-main` tiene `padding-top: 90px` para compensar el header fijo

---

## Por qué fallé hoy — análisis

### Error raíz: manipular `padding-top` desde JS leyendo `offsetHeight`

El header tiene `transition: height 0.35s ease` en `.es-header__row`. Cuando el JS lee
`header.offsetHeight` justo después de aplicar/quitar `is-scrolled`, obtiene el valor
**a mitad de la transición CSS** (la animación aún no terminó). Resultado: `padding-top`
queda con un valor incorrecto y el contenido se desplaza mal.

Intenté parchar esto con:
1. `getTargetHeight()` calculando la altura final manualmente → incorrecto porque hardcodeé
   valores y fallé en casos de mobile/resize
2. Bucle `requestAnimationFrame` por 400ms → solucionó el timing pero rompió el layout
   incluso sin banner (el `offsetHeight` inicial podría leerse mal antes del primer paint)
3. `transition: padding-top 0.35s ease` en `.es-main` → problemas en el primer render

Cada parche añadió fragilidad. La causa profunda: **no hay que leer `offsetHeight` nunca
para gestionar el `padding-top`**. El navegador ya sabe sincronizar transiciones CSS.

### La solución correcta (no ejecutada hoy)

Usar el **selector sibling CSS** para que el padding lo gestione el navegador, sin JS:

```css
/* Base (desktop, sin scroll) */
.es-main {
    padding-top: 90px;          /* ajustar si hay banner */
    transition: padding-top 0.35s ease;
}

/* Cuando el header tiene is-scrolled */
.es-header.is-scrolled ~ .es-main {
    padding-top: 52px;          /* ajustar si hay banner */
}

/* Mobile siempre 52px */
@media (max-width: 1024px) {
    .es-main { padding-top: 52px; }
}
```

El JS **solo** togglea la clase `is-scrolled`. El navegador aplica la regla CSS con
la misma curva `0.35s ease`, perfectly in sync con la transición del row. No hay leer
alturas, no hay timing issues, no hay rAF.

---

## Plan de implementación (para mañana)

### Paso 0 — verificar que el tema activo es custom y el layout está limpio

```bash
git status   # debe estar limpio
# abrir http://localhost:8081 y confirmar que el header fijo funciona bien
# scroll down → row se achica; scroll up → vuelve a 90px. Sin banner, esto debe funcionar PERFECTO antes de tocar nada.
```

### Paso 1 — Capa de aplicación (sin tocar templates ni CSS)

Crear en `app/src/Application/Setting/`:

**`GetAnnouncement/GetAnnouncementService.php`**
```
execute() → array{enabled: bool, text: string, color: string}
Defaults: enabled=false, text='', color='amber'
Lee: announcement_enabled, announcement_text, announcement_color
```

**`UpdateAnnouncement/UpdateAnnouncementCommand.php`**
```
public static function create(bool $enabled, string $text, string $color): self
```

**`UpdateAnnouncementService.php`**
```
execute(UpdateAnnouncementCommand $command): void
Upsert de los 3 keys en settings
```

Registrar los servicios en `services.yaml` si hace falta (autowire debería bastarlo).

**Verificar:** `docker compose exec php-cli php bin/phpunit` — tests verdes.

### Paso 2 — Exponer en Twig globals

En `ActiveThemeExtension::getGlobals()`:
```php
'announcement' => $this->getAnnouncementService->execute(),
```
Con try/catch que devuelve defaults si la BD falla.

**Verificar:** `{{ dump(announcement) }}` en un template → ve el array.

### Paso 3 — CSS: una variable custom para la altura del banner

Esto es clave para no hardcodear la altura en múltiples sitios. En el `<style>` inline
del `themes/custom/layout.html.twig`, añadir en `:root`:

```css
--announcement-h: {% if announcement.enabled and announcement.text %}3.6rem{% else %}0px{% endif %};
```

La altura del banner (padding 1rem top + 1.6rem line-height + 1rem bottom + 1px border)
es exactamente `3.6rem + 1px` para texto de una línea. Si el texto es más largo y salta
a dos líneas, habrá un pequeño solape, pero es aceptable.

Luego cambiar las reglas de `.es-main` a:
```css
.es-main {
    padding-top: calc(90px + var(--announcement-h));
    transition: padding-top 0.35s ease;
}
.es-header.is-scrolled ~ .es-main {
    padding-top: calc(52px + var(--announcement-h));
}
@media (max-width: 1024px) {
    .es-main { padding-top: calc(52px + var(--announcement-h)); }
}
```

**Verificar sin banner activo:** todo igual que antes. `--announcement-h: 0px` → mismos
valores de antes.

### Paso 4 — Banner en el HTML del header

En `themes/custom/layout.html.twig`, dentro de `<header class="es-header">`,
**después** de `</div>{# /.es-header__row #}`:

```twig
{% if announcement.enabled and announcement.text %}
    <div class="announcement-banner announcement-banner--{{ announcement.color }}">
        {{ announcement.text }}
    </div>
{% endif %}
```

**Verificar con banner activo:** activar desde el backoffice (o forzar en Twig),
confirmar que:
- Aparece permanente al scrollear
- No tapa las categorías ni al cargar ni al scrollear arriba/abajo

### Paso 5 — CSS del banner en `main.css`

```css
.announcement-banner {
    padding: 1rem 2rem;
    text-align: center;
    font-size: 1rem;
    line-height: 1.6;
    letter-spacing: 0.02em;
    border-bottom: 1px solid #000;
    width: 100%;
}
.announcement-banner--amber { background: radial-gradient(ellipse at center, #FFFEF7 0%, #FEF3C7 100%); color: #78350F; }
.announcement-banner--sky   { background: radial-gradient(ellipse at center, #F5F9FF 0%, #DBEAFE 100%); color: #1E3A8A; }
.announcement-banner--sage  { background: radial-gradient(ellipse at center, #F5FDF7 0%, #D1FAE5 100%); color: #14532D; }
.announcement-banner--rose  { background: radial-gradient(ellipse at center, #FDF8FF 0%, #F3E8FF 100%); color: #581C87; }
.announcement-banner--sand  { background: radial-gradient(ellipse at center, #FDFCFA 0%, #EDE9E3 100%); color: #292524; }
```

### Paso 6 — Backoffice: formulario en Personalización

En `AdminPersonalizacionController.php`:
- GET `/admin/personalizacion` → renderizar con `announcement` en el contexto
- POST `/admin/personalizacion/announcement` → `UpdateAnnouncementCommand`, flash, redirect

En `admin/personalizacion/index.html.twig`, añadir sección "Aviso en galería":
- Checkbox `announcement_enabled`
- Textarea `announcement_text`
- Radio buttons de color (swatches de colores, styled como círculos)
- Previsualizador ~420px con JS live-preview (colorMap + texto)
- CSRF token `update_announcement`

### Paso 7 — Tests y commit

```bash
docker compose exec -T php-cli php bin/phpunit
```

Si pasan → avisar al usuario para revisar y dar el OK para commitear.

---

## Qué NO tocar

- `app/src/Infrastructure/Http/View/public/layout.html.twig` (tema default — no está activo en prod)
- El JS de `updateLayout()` — solo debe togglear `is-scrolled`, nada más
- `padding-top` de `.es-main` vía JS inline — **prohibido**
