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

COPY . .

RUN npm install && npm run build
RUN php artisan filament:assets --no-interaction
RUN chmod -R 775 storage bootstrap/cache
RUN php artisan shield:generate --all --panel=admin --option=[yes,yes] \
    && php artisan shield:super-admin --user=admin@example.com --no-interaction; \

EXPOSE 8080

CMD php artisan config:clear \
    && php artisan config:cache \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
    # && php artisan migrate --force \
    # && php artisan db:seed --force \
