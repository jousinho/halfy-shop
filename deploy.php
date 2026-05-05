<?php

namespace Deployer;

require 'recipe/symfony.php';

// ─── Repositorio ─────────────────────────────────────────────────────────────

set('repository', 'git@github.com:jousinho/halfy-shop.git');
set('branch', 'main');
set('keep_releases', 3);
set('git_tty', false);

// ─── Paths (Symfony vive en app/, no en la raíz del repo) ────────────────────

set('app_path', '{{release_path}}/app');
set('bin/console', '{{app_path}}/bin/console');
set('public_path', '{{app_path}}/public');

// ─── Archivos y directorios compartidos entre releases ───────────────────────
// Shared files persisten en /halfyshop/shared/ y se enlazan en cada release.
// Aquí van el .env.local con credenciales de producción y los uploads.

set('shared_files', [
    'app/.env.local',
]);

set('shared_dirs', [
    'app/var/log',
    'app/public/uploads',
]);

// ─── Directorios que necesitan permisos de escritura ─────────────────────────

set('writable_dirs', [
    'app/var',
    'app/public/uploads',
]);

// ─── Hosts ───────────────────────────────────────────────────────────────────

// Subdominio temporal para revisión con Anna antes del go-live
host('dev')
    ->setHostname('89.140.72.166')
    ->setPort(22)
    ->setRemoteUser('annapo_ftp')
    ->setDeployPath('/dev.annapownall.com')
    ->set('keep_releases', 1);

// Dominio principal — usar solo cuando Anna dé el OK
host('production')
    ->setHostname('89.140.72.166')
    ->setPort(22)
    ->setRemoteUser('annapo_ftp')
    ->setDeployPath('/halfyshop')
    ->set('keep_releases', 3);

// ─── Tareas personalizadas ────────────────────────────────────────────────────

// Composer install dentro de app/ (donde vive el composer.json)
task('deploy:vendors', function () {
    run('cd {{app_path}} && {{bin/composer}} install --no-dev --optimize-autoloader --no-interaction');
});

// Migraciones automáticas en cada deploy
task('database:migrate', function () {
    run('cd {{app_path}} && {{bin/php}} {{bin/console}} doctrine:migrations:migrate --no-interaction --env=prod');
});

// Cache warmup de producción
task('deploy:cache:warmup', function () {
    run('cd {{app_path}} && {{bin/php}} {{bin/console}} cache:warmup --env=prod');
});

// ─── Flujo de deploy ──────────────────────────────────────────────────────────

task('deploy', [
    'deploy:info',       // muestra repo y branch
    'deploy:setup',      // crea estructura en servidor si no existe
    'deploy:lock',       // evita deploys concurrentes
    'deploy:release',    // crea directorio releases/N
    'deploy:update_code',// git clone/pull
    'deploy:shared',     // enlaza shared files y dirs
    'deploy:writable',   // ajusta permisos
    'deploy:vendors',    // composer install
    'database:migrate',  // doctrine:migrations:migrate
    'deploy:cache:warmup',
    'deploy:symlink',    // cambia current -> releases/N (zero-downtime)
    'deploy:unlock',
    'deploy:cleanup',    // borra releases antiguas (keep_releases: 3)
    'deploy:success',
]);

// Si el deploy falla, liberar el lock para poder volver a deployar
after('deploy:failed', 'deploy:unlock');
