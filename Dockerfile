FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Install sistem dependencies
RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    dom \
    xml \
    gd \
    bcmath \
    pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files (cache layer)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy package files (cache layer)
COPY package.json package-lock.json ./
RUN npm ci

# Copy semua source code
COPY . .

# Build frontend assets
RUN npm run build

# Jalankan post-install scripts Laravel
RUN composer run-script post-autoload-dump || true

# Set permission
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy config
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080
CMD ["/start.sh"]