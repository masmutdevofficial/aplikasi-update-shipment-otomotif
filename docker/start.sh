#!/usr/bin/env sh
set -e

cd /var/www/html

# Generate APP_KEY jika belum ada
if grep -q "APP_KEY=$" .env; then
    php artisan key:generate --force
fi

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Jalankan migrasi (aman, hanya apply migration yang belum dijalankan)
php artisan migrate --force

# Seed hanya jika tabel users benar-benar kosong (fresh install),
# agar tidak menimpa atau menduplikasi data di production yang sudah ada.
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1)
if [ "${USER_COUNT}" = "0" ] || [ -z "${USER_COUNT}" ]; then
    echo "Database kosong — menjalankan seeder awal..."
    php artisan db:seed --force
else
    echo "Database sudah berisi data (${USER_COUNT} user) — seeder dilewati."
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf