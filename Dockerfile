FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev

RUN docker-php-ext-install zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
