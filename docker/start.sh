#!/bin/sh
set -e

echo "=== Starting invoice-app ==="

mkdir -p /var/www/html/storage/logs \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

cd /var/www/html

php artisan storage:link --force || true

php artisan route:cache || echo "WARNING: route:cache failed, continuing..."
php artisan view:cache  || echo "WARNING: view:cache failed, continuing..."

if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "=== Waiting for Cloud SQL ==="
    RETRIES=15
    until php artisan db:show --no-interaction; do
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
        php artisan db:seed --force || true
    fi
fi

echo "=== Starting PHP-FPM ==="
php-fpm -D

until nc -z 127.0.0.1 9000; do
    echo "  Waiting for PHP-FPM..."
    sleep 0.5
done
echo "  PHP-FPM ready"

echo "=== Starting Nginx ==="
exec nginx -g 'daemon off;'