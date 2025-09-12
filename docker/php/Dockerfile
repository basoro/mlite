# mLITE Docker PHP 8.3 Environment
# Compatible with mLITE PHP 7.4 - 8.3+ requirements
FROM php:8.3-fpm

# Install system dependencies required for mLITE
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
    unzip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by mLITE (compatible with PHP 8.3)
RUN docker-php-ext-install \
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

# Configure GD extension with FreeType and JPEG support for mLITE image processing
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd 

# Install Redis for caching (optional but recommended for performance)
RUN pecl install redis && docker-php-ext-enable redis

# Install Xdebug for development debugging (PHP 8.3 compatible)
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install Composer for dependency management
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy custom PHP configuration optimized for mLITE
COPY ./php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Set working directory to mLITE application root
WORKDIR /var/www/public

# Copy and setup entrypoint script for mLITE initialization
COPY ./php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

# Expose PHP-FPM port
EXPOSE 9000
