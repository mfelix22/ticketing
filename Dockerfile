# ─── Stage 1: Node build (compile assets) ────────────────────────────────────
FROM node:22-alpine AS node-build

WORKDIR /app

COPY package*.json ./
RUN npm install --ignore-scripts

COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

RUN npm run build

# ─── Stage 2: PHP + Apache ────────────────────────────────────────────────────
FROM php:8.3-apache

# Enable Apache modules required by Laravel
RUN a2enmod rewrite headers

# Suppress ServerName warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# The app is served at the document root (index.php/.htaccess already live at
# the project root in this repo) for https://sdp-ticketing.hartonomotor-group.com
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
 && printf '<Directory /var/www/html>\n    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
 && a2enconf laravel

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg-dev \
        libwebp-dev \
        libfreetype6-dev \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
        zip \
        unzip \
        curl \
        git \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        xml \
        dom \
 && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_MEMORY_LIMIT=-1

WORKDIR /var/www/html

# Step 1: install dependencies (no autoloader yet — app source not copied yet)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs

# Step 2: copy full source
COPY . /var/www/html

# Step 3: merge public/ contents into the docroot (index.php/.htaccess already
# live at root in this repo) so files like /vendor/livewire/... resolve as real
# files, then drop in the freshly built vite assets in both locations
RUN cp -rp /var/www/html/public/. /var/www/html/
COPY --from=node-build /app/public/build /var/www/html/public/build
COPY --from=node-build /app/public/build /var/www/html/build

# Step 4: generate optimised autoloader now that all classes exist
RUN composer dump-autoload --optimize --no-dev --ignore-platform-reqs

# Make sure runtime directories exist (contents may be dockerignored) and set permissions
RUN mkdir -p /var/www/html/storage/app/public \
             /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html/storage \
 && chmod -R 755 /var/www/html/bootstrap/cache

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]
