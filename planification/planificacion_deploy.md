# Planificación de Deploy — HalfyShop

Estado actual: código listo en `main`, sin deployar. WordPress activo en Plesk (no tocar hasta go-live).

---

## Fase 1 — Preparar el código
> Hago yo

- [ ] Levantar Docker y pasar los tests (`docker compose up -d` + `php bin/phpunit`)
- [ ] Actualizar `deploy.php` — dos hosts: `app` (subdominio, `keep_releases: 1`) y `production` (`keep_releases: 3`)
- [ ] Commitear y pushear: `composer.json`, `composer.lock`, `deploy.php`, `scripts/`

---

## Fase 2 — Preparar el servidor
> Haces tú en Plesk + SSH

- [ ] Crear subdominio `app.annapownall.com` en Plesk
- [ ] Activar SSL (Let's Encrypt) en el subdominio
- [ ] Verificar que PHP 8.4 está disponible en Plesk → subdominio → PHP Settings
- [ ] Crear BD MySQL `halfyshop` y usuario (o reutilizar credenciales del WordPress)
- [ ] Generar hash del password de admin:
      `docker compose exec php-cli php bin/console security:hash-password`
- [ ] Generar APP_SECRET: `openssl rand -hex 32`
- [ ] Crear `/ruta-subdominio/shared/app/.env.local` con:
      ```
      APP_ENV=prod
      APP_SECRET=<generado>
      DATABASE_URL=mysql://USUARIO:PASSWORD@localhost:3306/halfyshop?serverVersion=8.0&charset=utf8mb4
      ADMIN_PASSWORD_HASH=<generado>
      BIGCARTEL_FEED_URL=https://annapownall.bigcartel.com/products.xml
      CONTACT_EMAIL=anna@annapownall.com
      ```
- [ ] Configurar document root en Plesk → apuntar a `current/app/public`

---

## Fase 3 — Primer deploy
> Hago yo (con tu confirmación)

- [ ] `dep deploy app`
- [ ] Verificar que carga `app.annapownall.com/es` y `app.annapownall.com/admin`
- [ ] Probar login de admin

---

## Fase 4 — Página under construction
> Hago yo el HTML, configuras tú en Plesk

- [ ] Crear página estática `under_construction.html` (estilo acorde a Anna)
- [ ] Configurar dominio principal `annapownall.com` en Plesk para servir esa página estática

---

## Resultado esperado

| URL | Qué ve |
|---|---|
| `annapownall.com` | Página under construction |
| `app.annapownall.com/es` | La web completa (Anna la revisa) |
| `app.annapownall.com/admin` | Panel de gestión de Anna |

---

## Notas

- **WordPress en Plesk**: no eliminar hasta que la web esté en producción y verificada.
  Contiene credenciales de BD y configuración útil de referencia.
- **Go-live**: cuando Anna dé el OK, `dep deploy production` + cambio de DNS/document root
  en `annapownall.com`. Luego se borra el subdominio y el directorio en el servidor.
- **Deployer**: instalado como dependencia de composer. Se lanza con `./scripts/deploy-prod.sh`
  o directamente `./app/vendor/bin/dep deploy app`.
