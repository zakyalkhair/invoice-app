# ============================================================
# Stage 1: Builder — install semua deps + build frontend
# ============================================================
FROM php:8.2-cli-alpine AS builder

WORKDIR /app

# Install build dependencies
RUN apk add --no-cache \
    nodejs \
    npm \
    curl \
    gcompat \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev \
    zip \
    unzip

# Install PHP extensions yang dibutuhkan composer install
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_mysql mbstring dom xml gd bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy dependency files dulu (cache layer — tidak reinstall jika tidak berubah)
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --no-interaction \
    --prefer-dist

# Copy package files
COPY package.json package-lock.json ./
RUN npm ci --no-audit

# Copy semua source code
COPY . .

# Build frontend assets (Vite + Tailwind)
RUN npm run build

# Jalankan post-autoload scripts setelah semua file ada
RUN composer run-script post-autoload-dump || true

# ============================================================
# Stage 2: Runtime — image ringan, hanya file yang diperlukan
# ============================================================
FROM php:8.2-fpm-alpine AS runtime

WORKDIR /var/www/html

# Install runtime dependencies saja (lebih kecil dari builder)
RUN apk add --no-cache \
    nginx \
    curl \
    libpng \
    libjpeg-turbo \
    freetype \
    libxml2 \
    oniguruma \
    zip \
    unzip

# Install PHP extensions
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        dom \
        xml \
        gd \
        bcmath \
        pcntl \
        opcache && \
    # Bersihkan dev headers setelah install
    apk del libpng-dev libjpeg-turbo-dev freetype-dev libxml2-dev oniguruma-dev

# OPcache config untuk production performance
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Salin hasil build dari stage builder
COPY --from=builder /app .

# Set permission yang benar
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 storage bootstrap/cache

# Pastikan direktori storage ada (penting untuk Cloud Run)
RUN mkdir -p storage/logs storage/framework/cache \
    storage/framework/sessions storage/framework/views \
    bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Copy konfigurasi Nginx dan start script
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Cloud Run listen di port 8080
EXPOSE 8080

CMD ["/start.sh"]