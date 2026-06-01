#!/bin/sh
set -e

echo "=== Starting invoice-app ==="

# 1. Pastikan direktori storage writable (Cloud Run filesystem ephemeral)
mkdir -p /var/www/html/storage/logs \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

cd /var/www/html

# 2. Storage link (idempotent)
php artisan storage:link --force 2>/dev/null || true

# 3. Cache config DULU — supaya artisan bisa baca env vars dengan benar
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Migrasi hanya untuk MySQL, dengan retry untuk tunggu Cloud SQL socket
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "=== Waiting for Cloud SQL socket... ==="
    
    RETRIES=10
    until php artisan db:show --no-interaction > /dev/null 2>&1 || [ $RETRIES -eq 0 ]; do
        echo "  Waiting for DB... ($RETRIES retries left)"
        sleep 2
        RETRIES=$((RETRIES - 1))
    done

    if [ $RETRIES -eq 0 ]; then
        echo "ERROR: Database tidak bisa diakses setelah retries. Abort."
        exit 1
    fi

    echo "=== Running migrations ==="
    php artisan migrate --force

    # Seeder opsional — hanya jalan jika tabel kosong atau kamu memang mau
    php artisan db:seed --class=MitraSeeder --force 2>/dev/null || true
fi

echo "=== Starting PHP-FPM ==="
php-fpm -D

echo "=== Starting Nginx ==="
exec nginx -g 'daemon off;'