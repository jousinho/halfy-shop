# Planificación de Deploy — HalfyShop

Última actualización: 2026-05-06

---

## Fase 1 — Preparar el código ✅ COMPLETADA

- [x] Tests pasando (186/186)
- [x] Commit y push en `main`
- [x] Página under construction (MaintenanceModeListener)
- [x] Thumbnails eliminados — solo imagen completa (1400px)
- [x] `.user.ini` en `public/` para OPcache validate_timestamps

---

## Fase 2 — Preparar el servidor ✅ COMPLETADA (dev.annapownall.com)

- [x] Subdominio `dev.annapownall.com` creado en Plesk
- [x] PHP 8.3.30 con FPM+Nginx configurado
- [x] BD MariaDB `HalfyShop` creada con usuario dedicado
- [x] `/dev.annapownall.com/shared/app/.env.local` creado con todas las variables
- [x] Document root → `/dev.annapownall.com/app/public`
- [x] Repositorio GitHub conectado en Plesk Git

> `dev.annapownall.com` queda como está. Ya no es el objetivo del deploy.

---

## Fase 3 — Deploy en `annapownall.com` ⏳ PENDIENTE

### Contexto

- WordPress está actualmente en `annapownall.com` — no borrarlo hasta go-live
- La BD `HalfyShop` que ya existe en el servidor se reutiliza (tiene datos del sync BigCartel)
- Acceso SSH disponible al servidor
- El under construction se activa solo al desplegar (el listener está en el código)

### Pasos

- [ ] En Plesk: conectar repo GitHub a `annapownall.com` → código en `annapownall.com/app/`
- [ ] Cambiar document root de `annapownall.com` → `annapownall.com/app/public/`
- [ ] Crear `.env.local` en el servidor (ver variables abajo)
- [ ] Instalar vendor: Plesk PHP Composer → `annapownall.com/app/`
- [ ] Por SSH: ejecutar migrations
  ```bash
  /opt/plesk/php/8.3/bin/php /annapownall.com/app/bin/console doctrine:migrations:migrate --no-interaction --env=prod
  ```
- [ ] Por SSH: warmup cache
  ```bash
  /opt/plesk/php/8.3/bin/php /annapownall.com/app/bin/console cache:warmup --env=prod
  ```
- [ ] Verificar que `annapownall.com` muestra el under construction
- [ ] Activar SSL (Let's Encrypt) en Plesk para `annapownall.com`
- [ ] Probar login admin en `annapownall.com/admin/login`

### Variables `.env.local` para producción

```
APP_ENV=prod
APP_SECRET=<generar aleatorio>
DATABASE_URL="mysql://<user>:<pass>@127.0.0.1:3306/HalfyShop?serverVersion=mariadb-5.5.68&charset=utf8mb4"
ADMIN_PASSWORD_HASH=<hash generado con security:hash-password>
BIGCARTEL_FEED_URL=https://annapownall.bigcartel.com/products.xml
CONTACT_EMAIL=<email de Anna>
```

---

## Fase 4 — Go live ⏳ PENDIENTE (cuando Anna dé el OK)

- [ ] Quitar `MaintenanceModeListener` (o desactivarlo con un parámetro de entorno)
- [ ] Verificar que el sitio público carga correctamente
- [ ] WordPress: ya no se sirve (document root cambiado) — se puede borrar

---

## Resultado esperado tras Fase 3

| URL | Qué ve |
|---|---|
| `annapownall.com` | Página under construction |
| `annapownall.com/admin` | Panel de gestión de Anna |
| `dev.annapownall.com` | Queda como está, sin uso activo |

---

## Notas

- **MariaDB**: el DATABASE_URL debe usar `serverVersion=mariadb-5.5.68`
- **OPcache**: `.user.ini` en `public/` activa `validate_timestamps` — no hace falta reiniciar PHP-FPM tras deploy
- **Imágenes**: al migrar de dev a prod, copiar `uploads/artworks/` al nuevo servidor si hay imágenes subidas manualmente. Las del sync de BigCartel se regeneran con el comando de sync.
- **Deployer**: descartado. Se usa Plesk Git nativo + SSH manual para migrations.
