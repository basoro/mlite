FROM php:8.3-apache

# --------------------------------------------------
# 🔥 FORCE SINGLE MPM (ANTI AH00534)
# --------------------------------------------------
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load \
          /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork

# Enable Apache rewrite
RUN a2enmod rewrite

# --------------------------------------------------
# System dependencies (mLITE)
# --------------------------------------------------
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libfreetype6-dev \
    zip \
    unzip \
    imagemagick \
    libmagickwand-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# PHP extensions
# --------------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    opcache

# --------------------------------------------------
# PECL extensions
# --------------------------------------------------
RUN pecl install imagick redis \
    && docker-php-ext-enable imagick redis

# --------------------------------------------------
# Composer
# --------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --------------------------------------------------
# Apache config
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

RUN mkdir -p uploads cache tmp \
    && chown -R www-data:www-data uploads cache tmp \
    && chmod -R 775 uploads cache tmp

EXPOSE 80
