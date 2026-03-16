FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    libicu-dev libzip-dev libpng-dev libjpeg-dev \
    libfreetype6-dev libonig-dev unzip git curl nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        intl zip gd pdo pdo_mysql \
        bcmath mbstring opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev --no-scripts --no-interaction

COPY package*.json ./
RUN npm install && npm run build

COPY . .

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD echo "=== ENV CHECK ===" \
    && echo "DB_HOST=$DB_HOST" \
    && echo "DB_DATABASE=$DB_DATABASE" \
    && php artisan config:clear \
    && php artisan config:cache \
    && php artisan migrate --force \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
