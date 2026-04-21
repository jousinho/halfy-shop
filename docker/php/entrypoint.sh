#!/bin/sh
set -e

mkdir -p /var/www/html/public/uploads/artworks/thumbnails
mkdir -p /var/www/html/public/uploads/novedades/thumbnails
chown -R www-data:www-data /var/www/html/public/uploads
chmod -R 775 /var/www/html/public/uploads

exec "$@"
