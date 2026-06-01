#!/bin/sh
set -e

echo "=== Starting invoice-app ==="

# 1. Pastikan direktori storage writable
mkdir -p /var/www/html/storage/logs \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

cd /var/www/html

# 2. Storage link
php artisan storage:link --force 2>/dev/null || true

# 3. Cache config — kalau gagal, lanjut saja (jangan kill container)
php artisan config:cache || echo "WARNING: config:cache failed, continuing..."
php artisan route:cache  || echo "WARNING: route:cache failed, continuing..."
php artisan view:cache   || echo "WARNING: view:cache failed, continuing..."

# 4. Migrasi dengan retry — jangan pakai set -e di sini
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "=== Waiting for Cloud SQL ==="
    RETRIES=15
    until php artisan db:show --no-interaction > /dev/null 2>&1; do
        RETRIES=$((RETRIES - 1))
        if [ $RETRIES -eq 0 ]; then
            echo "WARNING: DB tidak bisa diakses, skip migration"
            break
        fi
        echo "  Retrying... ($RETRIES left)"
        sleep 2
    done

    if [ $RETRIES -gt 0 ]; then
        echo "=== Running migrations ==="
        php artisan migrate --force || echo "WARNING: migrate failed"
        php artisan db:seed --force 2>/dev/null || true
    fi
fi

# 5. Start PHP-FPM dan tunggu sampai siap
echo "=== Starting PHP-FPM ==="
php-fpm -D

# Tunggu FPM benar-benar listen di 9000
until nc -z 127.0.0.1 9000 2>/dev/null; do
    echo "  Waiting for PHP-FPM..."
    sleep 0.5
done
echo "  PHP-FPM ready"

# 6. Start Nginx (foreground — ini yang bikin container tetap hidup)
echo "=== Starting Nginx ==="
exec nginx -g 'daemon off;'