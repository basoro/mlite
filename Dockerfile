FROM php:8.3-apache

# Enable Apache rewrite
RUN a2enmod rewrite

# --------------------------------------------------
# Install system dependencies required for mLITE
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
# 🔥 FIX MPM CONFLICT (WAJIB)
# --------------------------------------------------
RUN a2dismod mpm_event mpm_worker \
 && a2enmod mpm_prefork

# --------------------------------------------------
# Configure & install PHP extensions
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

RUN docker-php-ext-enable mysqli

# --------------------------------------------------
# PECL extensions
# --------------------------------------------------
RUN pecl install imagick redis \
    && docker-php-ext-enable imagick redis

# --------------------------------------------------
# Install Xdebug (DEV ONLY – comment for production)
# --------------------------------------------------
# RUN pecl install xdebug \
#     && docker-php-ext-enable xdebug

# --------------------------------------------------
# Install Composer
# --------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --------------------------------------------------
# Apache config
# --------------------------------------------------
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# --------------------------------------------------
# Application setup
# --------------------------------------------------
WORKDIR /var/www/html
COPY . .

# Install composer dependencies (if exists)
RUN if [ -f composer.json ]; then \
        composer install --no-dev --optimize-autoloader; \
    fi

# --------------------------------------------------
# Permissions (important for mLITE)
# --------------------------------------------------
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Writable directories
RUN mkdir -p uploads cache tmp \
    && chown -R www-data:www-data uploads cache tmp \
    && chmod -R 775 uploads cache tmp

EXPOSE 80
