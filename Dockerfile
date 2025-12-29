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

# --------------------------------------------------
# 🔥 FIX PHP SESSION (LOGIN LOOP FIX)
# --------------------------------------------------
RUN mkdir -p /var/lib/php/sessions \
    && chown -R www-data:www-data /var/lib/php \
    && chmod -R 700 /var/lib/php/sessions

# Custom PHP config (session)
COPY php.ini /usr/local/etc/php/conf.d/99-mlite.ini

# Ensure php-fpm runs as www-data
RUN sed -i 's/^user = .*/user = www-data/' /usr/local/etc/php-fpm.d/www.conf \
 && sed -i 's/^group = .*/group = www-data/' /usr/local/etc/php-fpm.d/www.conf

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

RUN mkdir -p backups uploads tmp \
    && chown -R www-data:www-data backups uploads tmp \
    && chmod -R 775 backups uploads tmp

RUN mkdir -p admin/tmp \
    && chown -R www-data:www-data admin/tmp \
    && chmod -R 775 admin/tmp

# --------------------------------------------------
# Entrypoint & Volume
# --------------------------------------------------
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Define volumes for persistence
VOLUME ["/var/www/html/backups", "/var/www/html/uploads"]

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord","-c","/etc/supervisor/conf.d/supervisord.conf"]
