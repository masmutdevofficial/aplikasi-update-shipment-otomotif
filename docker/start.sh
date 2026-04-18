#!/usr/bin/env sh
set -e

cd /var/www/html

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

exec /usr/bin/supervisord -c /etc/supervisord.conf