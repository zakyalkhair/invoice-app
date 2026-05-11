#!/bin/sh

# Generate config cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start php-fpm di background
php-fpm -D

# Start nginx di foreground
nginx -g "daemon off;"