# SPeEdtracQR — production image for Render (web + queue + cron share this image)
FROM php:8.3-apache

# --- System deps + PHP extensions -------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
        libonig-dev libpq-dev libxml2-dev curl ca-certificates gnupg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo pdo_pgsql pgsql gd zip bcmath mbstring exif pcntl \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node 20 (for `npm run build`)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# --- Apache: serve from public/ ---------------------------------------------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf \
    && a2enmod rewrite

WORKDIR /var/www/html

# --- App build ---------------------------------------------------------------
# Copy dependency manifests first for better layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && npm run build \
    && rm -rf node_modules \
    && chown -R www-data:www-data storage bootstrap/cache

# Render injects $PORT; Apache must listen on it
RUN sed -ri 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
    && sed -ri 's/:80>/:${PORT}>/' /etc/apache2/sites-available/000-default.conf

COPY docker/start-web.sh /usr/local/bin/start-web.sh
RUN chmod +x /usr/local/bin/start-web.sh

EXPOSE 8080
CMD ["start-web.sh"]
