#!/bin/sh
set -e

echo "==> Starting invoice-app..."

# Run storage link (idempotent, aman dijalankan berulang)
php artisan storage:link --force 2>/dev/null || true

# Buat SQLite dummy supaya Laravel tidak crash
touch /tmp/database.sqlite
php artisan migrate --force 2>/dev/null || true

# Optimize config/route/view cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Starting PHP-FPM..."
php-fpm -D

echo "==> Starting Nginx..."
exec nginx -g "daemon off;"