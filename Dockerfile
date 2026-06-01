FROM php:8.2-cli-alpine AS builder

WORKDIR /app

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
    icu-dev \
    zip \
    unzip

RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-configure intl && \
    docker-php-ext-install pdo pdo_mysql mbstring dom xml gd bcmath intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --no-interaction \
    --prefer-dist

COPY package.json package-lock.json ./
RUN npm ci --no-audit

COPY . .

RUN npm run build

RUN composer run-script post-autoload-dump || true

FROM php:8.2-fpm-alpine AS runtime

WORKDIR /var/www/html

RUN apk add --no-cache \
    nginx \
    curl \
    netcat-openbsd \
    libpng \
    libjpeg-turbo \
    freetype \
    libxml2 \
    oniguruma \
    icu-libs \
    icu-data-full \
    zip \
    unzip

RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev \
    icu-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-configure intl && \
    docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        dom \
        xml \
        gd \
        bcmath \
        pcntl \
        intl \
        opcache && \
    apk del libpng-dev libjpeg-turbo-dev freetype-dev libxml2-dev oniguruma-dev icu-dev

RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

COPY --from=builder /app .

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 storage bootstrap/cache

RUN mkdir -p storage/logs storage/framework/cache \
    storage/framework/sessions storage/framework/views \
    bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]