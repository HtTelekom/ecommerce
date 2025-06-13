FROM php:8.2-fpm

WORKDIR /var/www

# Sistem bağımlılıklarını yükle
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    pkg-config \
    zip \
    unzip

# PHP extensions yükle
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# MongoDB extension - uyumlu versiyon yükle
RUN pecl install mongodb-1.17.3 && docker-php-ext-enable mongodb

# Composer yükle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Uygulamayı kopyala
COPY . /var/www

# Composer paketlerini yükle - platform req ignore
RUN composer install --optimize-autoloader --no-dev --ignore-platform-reqs

# Dosya izinlerini düzelt
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Port aç
EXPOSE 8000

# Uygulamayı başlat
CMD php artisan serve --host=0.0.0.0 --port=8000