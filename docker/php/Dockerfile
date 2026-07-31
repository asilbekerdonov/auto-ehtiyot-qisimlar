FROM php:8.4-fpm

# Системные зависимости для нужных PHP-расширений
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Расширения, нужные Laravel + maatwebsite/excel
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    gd \
    bcmath \
    exif

# Composer (просто копируем бинарник из официального образа)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Сначала только манифесты — чтобы Docker кэшировал composer install,
# пока меняется только код, а не зависимости
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Остальной код проекта
COPY . .

RUN composer dump-autoload --optimize \
    && php artisan storage:link || true

# Права на папки, куда Laravel пишет (кэш, логи, сессии)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]