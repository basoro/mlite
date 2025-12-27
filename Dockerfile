FROM php:8.3-fpm

# --------------------------------------------------
# System dependencies
# --------------------------------------------------
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libfreetype6-dev \
    imagemagick \
    libmagickwand-dev \
    zip \
    unzip \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# PHP extensions (mLITE)
# --------------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    bcmath \
    gd \
    intl \
    zip \
    opcache

RUN pecl install imagick redis \
 && docker-php-ext-enable imagick redis

# --------------------------------------------------
# Composer
# --------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --------------------------------------------------
# Nginx config
# --------------------------------------------------
COPY nginx.conf /etc/nginx/nginx.conf

# --------------------------------------------------
# Supervisor
# --------------------------------------------------
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# --------------------------------------------------
# App
# --------------------------------------------------
WORKDIR /var/www/html
COPY . .

RUN if [ -f composer.json ]; then \
        composer install --no-dev --optimize-autoloader; \
    fi

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

RUN mkdir -p uploads tmp \
    && chown -R www-data:www-data uploads tmp \
    && chmod -R 775 uploads tmp

RUN mkdir -p admin/tmp \
    && chown -R www-data:www-data admin/tmp \
    && chmod -R 775 admin/tmp

EXPOSE 80

CMD ["/usr/bin/supervisord"]
