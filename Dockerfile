# Use official PHP image

FROM php:8.3-cli

# Install system dependencies

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zip \
    && docker-php-ext-install zip


# Install Composer

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory

WORKDIR /app

# Copy project files

COPY . .

# Install PHP dependencies

RUN composer install --no-dev --optimize-autoloader

# Generate Laravel caches

RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

# Expose Render's port

EXPOSE 10000

# Start Laravel server

CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
