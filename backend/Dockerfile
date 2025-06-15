FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libssl-dev \
    pkg-config

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install MongoDB extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Change ownership
RUN chown -R www-data:www-data /var/www

# Expose port 8000
EXPOSE 8000

# Start PHP development server
CMD php artisan serve --host=0.0.0.0 --port=8000
