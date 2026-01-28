FROM php:8.3-fpm

# Install package yang dibutuhkan + nginx + supervisor
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        mbstring \
        exif \
        bcmath \
        gd \
        intl \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP settings untuk mLITE
RUN echo "upload_max_filesize=50M" > /usr/local/etc/php/conf.d/mlite.ini \
    && echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/mlite.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/mlite.ini \
    && echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/mlite.ini \
    && echo "date.timezone=Asia/Jakarta" >> /usr/local/etc/php/conf.d/mlite.ini

# Aktifkan OPcache
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini

# Copy konfigurasi Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/site.conf /etc/nginx/conf.d/default.conf

# Copy supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working dir
WORKDIR /var/www/html

# Copy source code mLITE
COPY . /var/www/html

# Permission penting
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["/usr/bin/supervisord", "-n"]
