FROM php:8.3-fpm

# --------------------------------------------------
# System dependencies (mLITE)
# --------------------------------------------------
RUN apt-get update && apt-get install -y \
    apache2 \
    apache2-utils \
    libapache2-mod-fcgid \
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
    && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# Apache config (MPM EVENT)
# --------------------------------------------------
RUN a2enmod proxy proxy_fcgi rewrite headers \
 && a2enconf php8.3-fpm \
 && a2dismod mpm_prefork \
 && a2enmod mpm_event

# --------------------------------------------------
# PHP extensions
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
# Apache vhost
# --------------------------------------------------
COPY apache.conf /etc/apache2/sites-available/000-default.conf

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

# --------------------------------------------------
# Start Apache + PHP-FPM
# --------------------------------------------------
CMD service php8.3-fpm start && apachectl -D FOREGROUND
